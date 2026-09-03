<?php

declare(strict_types=1);

namespace App\Modules\Channels\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Plan;
use App\Models\TelegramChannel;
use Illuminate\Database\Eloquent\Collection;

class ChannelPresentation extends Presentation implements PresentationInterface
{
    /** @return array<int|string, mixed> */
    public function present(mixed $data): array
    {
        if ($data instanceof Collection) {
            /** @var Collection<int, TelegramChannel> $data */
            return $data->map(fn (TelegramChannel $channel): array => $this->presentChannel($channel))->values()->all();
        }

        if (! $data instanceof TelegramChannel) {
            return parent::present($data);
        }

        return $this->presentChannel($data);
    }

    /** @return array<string, mixed> */
    private function presentChannel(TelegramChannel $channel): array
    {
        return [
            'id' => $channel->id,
            'owner_id' => $channel->owner_id,
            'telegram_chat_id' => $channel->telegram_chat_id,
            'username' => $channel->username,
            'title' => $channel->title,
            'description' => $channel->description,
            'status' => $channel->status,
            'is_available' => $channel->status === 'active' && $channel->relationLoaded('plans') && $channel->plans->contains(fn (Plan $plan): bool => $plan->is_active),
            'owner' => $channel->relationLoaded('owner') && $channel->owner !== null ? [
                'id' => $channel->owner->id,
                'name' => $channel->owner->name,
                'email' => $channel->owner->email,
            ] : null,
            'plans' => $channel->relationLoaded('plans') ? $channel->plans->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'code' => $plan->code,
                'name' => $plan->name,
                'price_minor' => $plan->price_minor,
                'currency' => $plan->currency,
                'duration_days' => $plan->duration_days,
                'is_active' => $plan->is_active,
            ])->values()->all() : [],
            'created_at' => $channel->created_at?->toIso8601String(),
            'updated_at' => $channel->updated_at?->toIso8601String(),
        ];
    }
}
