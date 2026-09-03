<?php

declare(strict_types=1);

namespace App\Modules\Channels\Database\Seeders;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TelegramTestDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') || ! config('telegram.test_data.enabled')) {
            return;
        }

        $owner = User::query()
            ->where('email', (string) config('telegram.test_data.owner_email'))
            ->first();

        if ($owner === null) {
            $this->command->warn('Skipping Telegram test data: configured owner user does not exist.');

            return;
        }

        TelegramChannel::query()->updateOrCreate(
            ['telegram_chat_id' => (string) config('telegram.test_data.group.chat_id')],
            [
                'owner_id' => $owner->id,
                'username' => $this->nullableString(config('telegram.test_data.group.username')),
                'title' => (string) config('telegram.test_data.group.title'),
                'description' => 'Test Telegram group seeded for local development.',
                'status' => 'active',
            ],
        );

        $bot = User::query()->firstOrCreate(
            ['email' => (string) config('telegram.test_data.bot.email')],
            [
                'name' => (string) config('telegram.test_data.bot.name'),
                'password' => Str::random(64),
            ],
        );

        UserIdentity::query()->updateOrCreate(
            [
                'provider' => 'telegram',
                'provider_user_id' => (string) config('telegram.test_data.bot.telegram_id'),
            ],
            [
                'user_id' => $bot->id,
                'username' => $this->nullableString(config('telegram.test_data.bot.username')),
                'meta' => ['is_bot' => true, 'seeded' => true],
            ],
        );
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? ltrim($value, '@') : null;
    }
}
