<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Payments\Enums\Permission as PaymentPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_crud_payment(): void
    {
        $manager = $this->createManagerUser(PaymentPermission::values());
        Sanctum::actingAs($manager);

        $subscription = Subscription::factory()->create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => Plan::factory()->create()->id,
        ]);

        $storeResponse = $this->postJson('/api/payments', [
            'subscription_id' => $subscription->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_1001',
            'status' => 'pending',
            'amount_minor' => 1999,
            'currency' => 'usd',
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $id = (int) $storeResponse->json('data.id');

        $this->patchJson("/api/payments/{$id}", [
            'status' => 'paid',
            'paid_at' => now()->toIso8601String(),
        ])->assertOk()->assertJsonPath('data.status', 'paid');

        $this->getJson("/api/payments/{$id}")
            ->assertOk()
            ->assertJsonPath('data.subscription_id', $subscription->id);

        $this->deleteJson("/api/payments/{$id}")
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing((new Payment)->getTable(), ['id' => $id]);
    }

    public function test_user_without_permission_cannot_create_payment(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $subscription = Subscription::factory()->create();

        $this->postJson('/api/payments', [
            'subscription_id' => $subscription->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount_minor' => 100,
            'currency' => 'USD',
        ])->assertForbidden();
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