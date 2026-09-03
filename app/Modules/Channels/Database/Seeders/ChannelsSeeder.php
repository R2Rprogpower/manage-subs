<?php

declare(strict_types=1);

namespace App\Modules\Channels\Database\Seeders;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\Enums\Permission as ChannelPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ChannelsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ChannelPermission::values() as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        if (app()->environment('production')) {
            return;
        }

        $owner = User::query()->where('email', 'test@example.com')->first();
        if ($owner !== null) {
            TelegramChannel::query()->firstOrCreate(
                ['telegram_chat_id' => '-1001234567890'],
                ['owner_id' => $owner->id, 'username' => 'demo_channel', 'title' => 'Demo Telegram Channel', 'description' => 'Demo channel for local development.', 'status' => 'active']
            );
        }
    }
}
