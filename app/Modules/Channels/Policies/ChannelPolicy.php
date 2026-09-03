<?php

declare(strict_types=1);

namespace App\Modules\Channels\Policies;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\Enums\Permission as ChannelPermission;

class ChannelPolicy
{
    public function view(User $user, TelegramChannel $channel): bool
    {
        return $channel->owner_id === $user->id || $user->can(ChannelPermission::VIEW_CHANNELS->value);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TelegramChannel $channel): bool
    {
        return $channel->owner_id === $user->id || $user->can(ChannelPermission::UPDATE_CHANNELS->value);
    }

    public function delete(User $user, TelegramChannel $channel): bool
    {
        return $channel->owner_id === $user->id || $user->can(ChannelPermission::DELETE_CHANNELS->value);
    }
}
