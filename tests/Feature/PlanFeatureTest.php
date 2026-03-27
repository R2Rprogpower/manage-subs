<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PlanFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_crud_plan(): void
    {
        $manager = $this->createManagerUser(PlanPermission::values());
        Sanctum::actingAs($manager);

        $storeResponse = $this->postJson('/api/plans', [
            'code' => 'partner_monthly',
            'name' => 'Partner Monthly',
            'price_minor' => 1499,
            'currency' => 'usd',
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.code', 'partner_monthly');

        $id = (int) $storeResponse->json('data.id');

        $this->patchJson("/api/plans/{$id}", [
            'name' => 'Partner Monthly Plus',
            'is_active' => false,
        ])->assertOk()->assertJsonPath('data.is_active', false);

        $this->getJson('/api/plans')
            ->assertOk()
            ->assertJsonFragment(['code' => 'partner_monthly']);

        $this->deleteJson("/api/plans/{$id}")
            ->assertOk()
            ->assertJsonPath('data.success', true);
    }

    public function test_user_without_permission_cannot_create_plan(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/plans', [
            'code' => 'locked',
            'name' => 'Locked',
            'price_minor' => 100,
            'currency' => 'USD',
            'is_active' => true,
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