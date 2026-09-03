<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SubscriptionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_crud_subscription(): void
    {
        $manager = $this->createManagerUser(SubscriptionPermission::values());
        Sanctum::actingAs($manager);

        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        $storeResponse = $this->postJson('/api/subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->toIso8601String(),
            'ends_at' => now()->addDays(30)->toIso8601String(),
            'auto_renew' => true,
            'trial_used' => false,
            'source' => 'bot',
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.status', 'active');

        $id = (int) $storeResponse->json('data.id');

        $this->patchJson("/api/subscriptions/{$id}", [
            'status' => 'cancelled',
            'auto_renew' => false,
        ])->assertOk()->assertJsonPath('data.status', 'cancelled');

        $this->getJson("/api/subscriptions/{$id}")
            ->assertOk()
            ->assertJsonPath('data.plan_id', $plan->id);

        $this->deleteJson("/api/subscriptions/{$id}")
            ->assertOk()
            ->assertJsonPath('data.success', true);
    }

    public function test_user_without_permission_cannot_create_subscription(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        $this->postJson('/api/subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->toIso8601String(),
            'auto_renew' => false,
            'trial_used' => false,
            'source' => 'manual',
        ])->assertForbidden();
    }

    public function test_manager_can_cancel_subscription_and_action_is_audited(): void
    {
        $manager = $this->createManagerUser(SubscriptionPermission::values());
        Sanctum::actingAs($manager);
        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->addMonth(),
        ]);

        $this->postJson("/api/subscriptions/{$subscription->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.has_access', false);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $manager->id,
            'action_type' => 'subscription_cancelled',
            'target_type' => 'subscription',
            'target_id' => $subscription->id,
        ]);
    }

    public function test_user_without_permission_cannot_list_all_subscriptions(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Subscription::factory()->create();

        $this->getJson('/api/subscriptions')->assertForbidden();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function createManagerUser(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permissionName) {
            $permission = Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $user->givePermissionTo($permission);
        }

        return $user;
    }
}
