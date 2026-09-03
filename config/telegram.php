<?php

declare(strict_types=1);

return [
    'test_data' => [
        'enabled' => (bool) env('TELEGRAM_TEST_SEED_ENABLED', true),
        'owner_email' => env('TELEGRAM_TEST_OWNER_EMAIL', 'test@example.com'),
        'group' => [
            'chat_id' => env('TELEGRAM_TEST_GROUP_CHAT_ID', '-1001234567890'),
            'username' => env('TELEGRAM_TEST_GROUP_USERNAME', 'demo_channel'),
            'title' => env('TELEGRAM_TEST_GROUP_TITLE', 'Demo Telegram Group'),
        ],
        'bot' => [
            'telegram_id' => env('TELEGRAM_TEST_BOT_ID', '987654321'),
            'username' => env('TELEGRAM_TEST_BOT_USERNAME', 'demo_subscription_bot'),
            'name' => env('TELEGRAM_TEST_BOT_NAME', 'Demo Subscription Bot'),
            'email' => env('TELEGRAM_TEST_BOT_EMAIL', 'telegram-bot@example.test'),
        ],
    ],
];
