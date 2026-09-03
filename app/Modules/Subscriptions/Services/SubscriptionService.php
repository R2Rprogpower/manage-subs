<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Services;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\UnprocessableEntityException;
use App\Infrastructure\Services\AuditLogService;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Plans\Contracts\Repositories\PlanRepositoryInterface;
use App\Modules\Subscriptions\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use App\Modules\Subscriptions\Enums\SubscriptionSource;
use App\Modules\Subscriptions\Enums\SubscriptionStatus;
use App\Modules\Subscriptions\Events\SubscriptionAccessChanged;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly PlanRepositoryInterface $planRepository,
        private readonly AuditLogService $auditLogService,
    ) {}

    /** @return Collection<int, Subscription> */
    public function findAll(): Collection
    {
        return $this->subscriptionRepository->findAll();
    }

    public function findById(int $id): ?Subscription
    {
        return $this->subscriptionRepository->findById($id);
    }

    /** @return Collection<int, Subscription> */
    public function findByUserId(int $userId): Collection
    {
        return $this->subscriptionRepository->findByUserId($userId);
    }

    public function create(CreateSubscriptionDTO $dto): Subscription
    {
        return $this->subscriptionRepository->create($dto);
    }

    public function subscribeWithPlaceholder(int $userId, int $planId): Subscription
    {
        $plan = $this->planRepository->findById($planId);
        if ($plan === null || ! $plan->is_active || $plan->telegramChannel === null) {
            throw new UnprocessableEntityException('Subscription type is unavailable.');
        }
        $channel = $plan->telegramChannel;
        if ($channel->status !== 'active') {
            throw new UnprocessableEntityException('Telegram channel is unavailable.');
        }
        if ($this->subscriptionRepository->findActiveByUserAndChannel($userId, $channel->id) !== null) {
            throw new ConflictException('An active subscription for this channel already exists.');
        }

        $user = User::query()->findOrFail($userId);
        $startedAt = now();
        $endsAt = $plan->duration_days !== null ? $startedAt->copy()->addDays($plan->duration_days) : null;

        return DB::transaction(function () use ($user, $plan, $channel, $startedAt, $endsAt): Subscription {
            TelegramChannel::query()->whereKey($channel->id)->lockForUpdate()->firstOrFail();
            if ($this->subscriptionRepository->findActiveByUserAndChannel($user->id, $channel->id) !== null) {
                throw new ConflictException('An active subscription for this channel already exists.');
            }

            $subscription = $this->subscriptionRepository->create(new CreateSubscriptionDTO(
                userId: $user->id,
                planId: $plan->id,
                status: SubscriptionStatus::ACTIVE->value,
                startedAt: $startedAt->toIso8601String(),
                endsAt: $endsAt?->toIso8601String(),
                autoRenew: false,
                trialUsed: false,
                source: SubscriptionSource::PLACEHOLDER->value,
            ));

            Payment::query()->create([
                'subscription_id' => $subscription->id,
                'provider' => 'placeholder',
                'provider_payment_id' => null,
                'status' => 'simulated',
                'amount_minor' => $plan->price_minor,
                'currency' => $plan->currency,
                'paid_at' => null,
            ]);

            $this->auditLogService->logSubscriptionAction(
                actor: $user,
                subscription: $subscription,
                action: 'subscription_placeholder_confirmed',
                newValue: ['status' => $subscription->status, 'provider' => 'placeholder'],
            );

            $this->dispatchAccessChanged($subscription);

            return $subscription->refresh()->load(['user', 'plan.telegramChannel', 'payments']);
        });
    }

    public function update(int $id, UpdateSubscriptionDTO $dto): Subscription
    {
        $subscription = $this->requireSubscription($id);
        $this->subscriptionRepository->update($subscription, $dto);

        return $subscription->refresh()->load(['user', 'plan.telegramChannel', 'payments']);
    }

    public function delete(int $id): void
    {
        $this->subscriptionRepository->delete($this->requireSubscription($id));
    }

    public function hasActiveAccess(int $userId, ?DateTimeInterface $at = null): bool
    {
        return $this->subscriptionRepository->findActiveByUserId($userId, $at) !== null;
    }

    public function hasActiveChannelAccess(int $userId, int $channelId, ?DateTimeInterface $at = null): bool
    {
        return $this->subscriptionRepository->findActiveByUserAndChannel($userId, $channelId, $at) !== null;
    }

    public function expireLapsedSubscriptions(?DateTimeInterface $at = null): int
    {
        $subscriptions = $this->subscriptionRepository->findLapsedActive($at);
        foreach ($subscriptions as $subscription) {
            $this->subscriptionRepository->update($subscription, new UpdateSubscriptionDTO([
                'status' => SubscriptionStatus::EXPIRED->value,
                'auto_renew' => false,
            ]));
            $subscription->refresh()->load('plan');
            $this->dispatchAccessChanged($subscription);
        }

        return $subscriptions->count();
    }

    public function activateSubscription(int $subscriptionId, ?int $actorId = null): Subscription
    {
        $subscription = $this->requireSubscription($subscriptionId);
        if ($subscription->grantsAccess()) {
            throw new ConflictException('Subscription is already active.');
        }

        $changes = ['status' => SubscriptionStatus::ACTIVE->value];
        if ($subscription->ends_at !== null && $subscription->ends_at->isPast()) {
            $durationDays = $subscription->plan?->duration_days;
            $changes['ends_at'] = $durationDays !== null
                ? now()->addDays($durationDays)->toIso8601String()
                : null;
        }

        return $this->changeLifecycle($subscription, $changes, 'subscription_activated', $actorId);
    }

    public function cancelSubscription(int $subscriptionId, ?int $actorId = null): Subscription
    {
        $subscription = $this->requireSubscription($subscriptionId);
        if ($subscription->status !== SubscriptionStatus::ACTIVE->value) {
            throw new ConflictException('Only an active subscription can be cancelled.');
        }

        return $this->changeLifecycle($subscription, ['status' => SubscriptionStatus::CANCELLED->value, 'auto_renew' => false], 'subscription_cancelled', $actorId);
    }

    public function renewSubscription(int $subscriptionId, ?DateTimeInterface $newEndsAt = null, ?int $actorId = null): Subscription
    {
        $subscription = $this->requireSubscription($subscriptionId);
        $durationDays = $subscription->plan?->duration_days;
        if ($newEndsAt === null && $durationDays === null) {
            throw new UnprocessableEntityException('An end date is required for an unlimited subscription type.');
        }

        $base = $subscription->ends_at !== null && $subscription->ends_at->isFuture() ? $subscription->ends_at->copy() : now();
        $endsAt = $newEndsAt !== null ? Carbon::instance(\DateTime::createFromInterface($newEndsAt)) : $base->addDays((int) $durationDays);

        return $this->changeLifecycle($subscription, ['status' => SubscriptionStatus::ACTIVE->value, 'ends_at' => $endsAt->toIso8601String()], 'subscription_renewed', $actorId);
    }

    public function grantFreeAccess(int $userId, int $planId, ?int $actorId = null): Subscription
    {
        $plan = $this->planRepository->findById($planId);
        if ($plan === null || $plan->telegram_channel_id === null) {
            throw new UnprocessableEntityException('Subscription type is unavailable.');
        }
        if ($this->subscriptionRepository->findActiveByUserAndChannel($userId, $plan->telegram_channel_id) !== null) {
            throw new ConflictException('An active subscription for this channel already exists.');
        }

        $startedAt = now();
        $subscription = $this->subscriptionRepository->create(new CreateSubscriptionDTO(
            userId: $userId,
            planId: $planId,
            status: SubscriptionStatus::ACTIVE->value,
            startedAt: $startedAt->toIso8601String(),
            endsAt: $plan->duration_days !== null ? $startedAt->copy()->addDays($plan->duration_days)->toIso8601String() : null,
            autoRenew: false,
            trialUsed: false,
            source: SubscriptionSource::ADMIN->value,
        ));

        $actor = $actorId !== null ? User::query()->find($actorId) : null;
        if ($actor !== null) {
            $this->auditLogService->logSubscriptionAction($actor, $subscription, 'subscription_free_access_granted', newValue: ['status' => $subscription->status]);
        }

        $this->dispatchAccessChanged($subscription);

        return $subscription;
    }

    public function syncChannelAccessForUser(int $userId, int $channelId, ?DateTimeInterface $at = null): bool
    {
        return $this->hasActiveChannelAccess($userId, $channelId, $at);
    }

    private function requireSubscription(int $id): Subscription
    {
        $subscription = $this->subscriptionRepository->findById($id);
        if ($subscription === null) {
            throw new \InvalidArgumentException("Subscription with ID {$id} not found.");
        }

        return $subscription;
    }

    /** @param array<string, mixed> $changes */
    private function changeLifecycle(Subscription $subscription, array $changes, string $action, ?int $actorId): Subscription
    {
        $previous = ['status' => $subscription->status, 'ends_at' => $subscription->ends_at?->toIso8601String()];

        return DB::transaction(function () use ($subscription, $changes, $action, $actorId, $previous): Subscription {
            $this->subscriptionRepository->update($subscription, new UpdateSubscriptionDTO($changes));
            $subscription->refresh()->load(['user', 'plan.telegramChannel', 'payments']);

            $actor = $actorId !== null ? User::query()->find($actorId) : null;
            if ($actor !== null) {
                $this->auditLogService->logSubscriptionAction($actor, $subscription, $action, $previous, [
                    'status' => $subscription->status,
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                ]);
            }

            $this->dispatchAccessChanged($subscription);

            return $subscription;
        });
    }

    private function dispatchAccessChanged(Subscription $subscription): void
    {
        $channelId = $subscription->plan?->telegram_channel_id;
        if ($channelId === null) {
            return;
        }

        event(new SubscriptionAccessChanged(
            subscriptionId: $subscription->id,
            userId: $subscription->user_id,
            channelId: $channelId,
            shouldHaveAccess: $this->hasActiveChannelAccess($subscription->user_id, $channelId),
        ));
    }
}
