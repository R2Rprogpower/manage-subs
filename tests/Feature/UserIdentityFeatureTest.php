<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\UserIdentities\Enums\Permission as UserIdentityPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserIdentityFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_crud_user_identity(): void
    {
        $manager = $this->createManagerUser(UserIdentityPermission::values());
        Sanctum::actingAs($manager);

        $user = User::factory()->create();

        $storeResponse = $this->postJson('/api/user-identities', [
            'user_id' => $user->id,
            'provider' => 'telegram',
            'provider_user_id' => 'tg-1001',
            'username' => 'first_handle',
            'meta' => ['lang' => 'en'],
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.provider', 'telegram');

        $id = (int) $storeResponse->json('data.id');

        $this->patchJson("/api/user-identities/{$id}", [
            'username' => 'second_handle',
            'meta' => ['lang' => 'fr'],
        ])->assertOk()->assertJsonPath('data.username', 'second_handle');

        $this->getJson("/api/user-identities/{$id}")
            ->assertOk()
            ->assertJsonPath('data.user_id', $user->id);

        $this->deleteJson("/api/user-identities/{$id}")
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing('user_identities', ['id' => $id]);
    }

    public function test_user_without_permission_cannot_create_user_identity(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $user = User::factory()->create();

        $this->postJson('/api/user-identities', [
            'user_id' => $user->id,
            'provider' => 'telegram',
            'provider_user_id' => 'tg-1002',
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
