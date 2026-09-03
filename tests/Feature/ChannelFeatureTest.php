<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\TelegramChannel;
use App\Models\User;
use App\Models\UserIdentity;
use App\Modules\Channels\Database\Seeders\TelegramTestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChannelFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_owner_can_register_channel(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);

        $this->postJson('/api/channels', [
            'telegram_chat_id' => '-100987654321',
            'username' => '@owner_channel',
            'title' => 'Owner Channel',
            'description' => 'Private insights',
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.owner_id', $owner->id)
            ->assertJsonPath('data.username', 'owner_channel')
            ->assertJsonPath('data.is_available', false);
    }

    public function test_channel_only_becomes_available_with_active_subscription_type(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $channel = TelegramChannel::factory()->create(['owner_id' => $user->id, 'status' => 'active']);

        $this->getJson('/api/channels/available')->assertOk()->assertJsonCount(0, 'data');

        Plan::factory()->create(['telegram_channel_id' => $channel->id, 'is_active' => true]);

        $this->getJson('/api/channels/available')
            ->assertOk()
            ->assertJsonPath('data.0.id', $channel->id)
            ->assertJsonPath('data.0.is_available', true);
    }

    public function test_user_cannot_modify_another_owners_channel(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $channel = TelegramChannel::factory()->create();

        $this->patchJson("/api/channels/{$channel->id}", ['title' => 'Hijacked'])
            ->assertForbidden();
    }

    public function test_owner_can_add_subscription_type_but_other_user_cannot(): void
    {
        $owner = User::factory()->create();
        $channel = TelegramChannel::factory()->create(['owner_id' => $owner->id]);
        $payload = [
            'telegram_channel_id' => $channel->id,
            'code' => 'channel_monthly',
            'name' => 'Monthly',
            'kind' => 'money',
            'price_minor' => 990,
            'currency' => 'USD',
            'duration_days' => 30,
            'is_active' => true,
        ];

        Sanctum::actingAs($owner);
        $this->postJson('/api/plans', $payload)->assertCreated();

        Sanctum::actingAs(User::factory()->create());
        $payload['code'] = 'foreign_channel_monthly';
        $this->postJson('/api/plans', $payload)->assertForbidden();
    }

    public function test_telegram_channel_identity_must_be_unique(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);
        $channel = TelegramChannel::factory()->create();

        $this->postJson('/api/channels', [
            'telegram_chat_id' => $channel->telegram_chat_id,
            'title' => 'Duplicate',
        ])->assertUnprocessable();
    }

    public function test_telegram_test_data_seeder_is_idempotent(): void
    {
        $owner = User::factory()->create(['email' => 'fixture-owner@example.test']);
        config([
            'telegram.test_data.enabled' => true,
            'telegram.test_data.owner_email' => $owner->email,
            'telegram.test_data.group.chat_id' => '-1009999999999',
            'telegram.test_data.group.username' => '@fixture_group',
            'telegram.test_data.group.title' => 'Fixture Group',
            'telegram.test_data.bot.telegram_id' => '99887766',
            'telegram.test_data.bot.username' => '@fixture_bot',
            'telegram.test_data.bot.name' => 'Fixture Bot',
            'telegram.test_data.bot.email' => 'fixture-bot@example.test',
        ]);

        $this->seed(TelegramTestDataSeeder::class);
        $this->seed(TelegramTestDataSeeder::class);

        $this->assertDatabaseCount('telegram_channels', 1);
        $this->assertDatabaseHas('telegram_channels', [
            'owner_id' => $owner->id,
            'telegram_chat_id' => '-1009999999999',
            'username' => 'fixture_group',
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('user_identities', 1);

        $identity = UserIdentity::query()->firstOrFail();
        $this->assertSame('99887766', $identity->provider_user_id);
        $this->assertSame('fixture_bot', $identity->username);
        $this->assertTrue((bool) $identity->meta['is_bot']);
    }
}
