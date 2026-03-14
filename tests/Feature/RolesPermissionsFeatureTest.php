<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Permissions\Enums\Permission as AppPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesPermissionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_manage_roles_permission_can_create_role(): void
    {
        $manager = $this->createManagerUser();
        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/roles', [
            'name' => 'editor',
            'guard_name' => 'web',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'editor');

        $this->assertDatabaseHas('roles', [
            'name' => 'editor',
            'guard_name' => 'web',
        ]);
    }

    public function test_user_without_manage_roles_permission_cannot_create_role(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/roles', [
            'name' => 'editor',
            'guard_name' => 'web',
        ])->assertForbidden();
    }

    public function test_manager_can_assign_permissions_to_role(): void
    {
        $manager = $this->createManagerUser();
        Sanctum::actingAs($manager);

        $role = Role::query()->create([
            'name' => 'editor',
            'guard_name' => 'web',
        ]);

        $permission = Permission::query()->create([
            'name' => 'manage-users',
            'guard_name' => 'web',
        ]);

        $response = $this->postJson("/api/roles/{$role->id}/permissions", [
            'permission_ids' => [$permission->id],
        ]);

        $response->assertOk();

        $this->assertTrue($role->fresh()->hasPermissionTo($permission));
    }

    public function test_manager_can_assign_role_to_user(): void
    {
        $manager = $this->createManagerUser();
        Sanctum::actingAs($manager);

        $role = Role::query()->create([
            'name' => 'editor',
            'guard_name' => 'web',
        ]);

        $targetUser = User::factory()->create();

        $response = $this->postJson("/api/users/{$targetUser->id}/roles", [
            'role_name' => $role->name,
        ]);

        $response->assertOk();

        $this->assertTrue($targetUser->fresh()->hasRole('editor'));
    }

    private function createManagerUser(): User
    {
        $manageRolesPermission = Permission::query()->firstOrCreate([
            'name' => AppPermission::MANAGE_ROLES->value,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo($manageRolesPermission);

        return $user;
    }
}
