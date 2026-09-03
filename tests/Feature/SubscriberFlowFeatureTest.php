<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\Events\SubscriptionAccessChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriberFlowFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriber_can_confirm_placeholder_and_see_only_own_subscription(): void
    {
        Event::fake([SubscriptionAccessChanged::class]);
        $subscriber = User::factory()->create();
        $other = User::factory()->create();
        $channel = TelegramChannel::factory()->create(['status' => 'active']);
        $plan = Plan::factory()->create([
            'telegram_channel_id' => $channel->id,
            'duration_days' => 30,
            'price_minor' => 1299,
            'currency' => 'USD',
            'is_active' => true,
        ]);
        Subscription::factory()->create(['user_id' => $other->id, 'plan_id' => $plan->id]);
        Sanctum::actingAs($subscriber);

        $response = $this->postJson('/api/subscriptions/checkout', [
            'plan_id' => $plan->id,
            'confirm_placeholder' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.source', 'placeholder')
            ->assertJsonPath('data.has_access', true)
            ->assertJsonPath('data.channel.id', $channel->id)
            ->assertJsonPath('metadata.message', 'Placeholder confirmed. No money was charged.');

        $subscriptionId = (int) $response->json('data.id');
        $this->assertDatabaseHas('payments', [
            'subscription_id' => $subscriptionId,
            'provider' => 'placeholder',
            'status' => 'simulated',
        ]);
        $this->assertNull(Payment::query()->findOrFail(1)->paid_at);
        Event::assertDispatched(SubscriptionAccessChanged::class, fn (SubscriptionAccessChanged $event): bool => $event->subscriptionId === $subscriptionId
            && $event->userId === $subscriber->id
            && $event->channelId === $channel->id
            && $event->shouldHaveAccess);

        $this->getJson('/api/subscriptions/mine')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $subscriber->id);
    }

    public function test_active_subscription_for_same_channel_cannot_be_duplicated(): void
    {
        $subscriber = User::factory()->create();
        $channel = TelegramChannel::factory()->create(['status' => 'active']);
        $firstPlan = Plan::factory()->create(['telegram_channel_id' => $channel->id, 'is_active' => true]);
        $secondPlan = Plan::factory()->create(['telegram_channel_id' => $channel->id, 'is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $subscriber->id,
            'plan_id' => $firstPlan->id,
            'status' => 'active',
            'ends_at' => now()->addDay(),
        ]);
        Sanctum::actingAs($subscriber);

        $this->postJson('/api/subscriptions/checkout', [
            'plan_id' => $secondPlan->id,
            'confirm_placeholder' => true,
        ])->assertConflict();
    }

    public function test_unavailable_channel_or_subscription_type_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $channel = TelegramChannel::factory()->create(['status' => 'unavailable']);
        $plan = Plan::factory()->create(['telegram_channel_id' => $channel->id, 'is_active' => true]);

        $this->postJson('/api/subscriptions/checkout', [
            'plan_id' => $plan->id,
            'confirm_placeholder' => true,
        ])->assertUnprocessable();
    }

    public function test_placeholder_rejects_payment_credentials(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $plan = Plan::factory()->create();

        $this->postJson('/api/subscriptions/checkout', [
            'plan_id' => $plan->id,
            'confirm_placeholder' => true,
            'card_number' => 'not-stored',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_access_is_scoped_to_channel_and_subscription_state(): void
    {
        $subscriber = User::factory()->create();
        $allowedChannel = TelegramChannel::factory()->create();
        $otherChannel = TelegramChannel::factory()->create();
        $plan = Plan::factory()->create(['telegram_channel_id' => $allowedChannel->id]);
        Subscription::factory()->create([
            'user_id' => $subscriber->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'ends_at' => now()->addDay(),
        ]);

        $service = app(SubscriptionServiceInterface::class);
        $this->assertTrue($service->hasActiveChannelAccess($subscriber->id, $allowedChannel->id));
        $this->assertFalse($service->hasActiveChannelAccess($subscriber->id, $otherChannel->id));
        $this->assertFalse($service->hasActiveChannelAccess($subscriber->id, $allowedChannel->id, now()->addDays(2)));
    }
}
