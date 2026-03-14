<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Permissions\DTO\CreateRoleDTO;
use App\Modules\Permissions\DTO\UpdateRoleDTO;
use App\Modules\Permissions\Repositories\RoleRepository;
use App\Modules\Permissions\Services\RoleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_creates_role_and_logs_when_actor_is_present(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/roles', 'POST');

        $dto = new CreateRoleDTO(name: 'manager', guardName: 'web');
        $role = $this->makeRole(id: 10, name: 'manager', guardName: 'web');

        $repository->shouldReceive('findByName')->once()->with('manager', 'web')->andReturn(null);
        $repository->shouldReceive('create')->once()->with($dto)->andReturn($role);
        $auditLogService->shouldReceive('logRoleCreation')->once()->with($actor, 10, 'manager', $request);

        $service = new RoleService($repository, $auditLogService);

        $result = $service->create($dto, $actor, $request);

        $this->assertSame($role, $result);
    }

    public function test_create_throws_when_role_already_exists(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $dto = new CreateRoleDTO(name: 'manager', guardName: 'web');
        $existingRole = $this->makeRole(id: 11, name: 'manager', guardName: 'web');

        $repository->shouldReceive('findByName')->once()->with('manager', 'web')->andReturn($existingRole);
        $repository->shouldNotReceive('create');

        $service = new RoleService($repository, $auditLogService);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Role 'manager' already exists.");

        $service->create($dto);
    }

    public function test_update_updates_role_and_logs_change(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/roles/7', 'PATCH');

        $dto = new UpdateRoleDTO(name: 'admin', guardName: 'web');
        $roleBefore = Mockery::mock(Role::class)->makePartial();
        $roleBefore->id = 7;
        $roleBefore->name = 'manager';
        $roleBefore->guard_name = 'web';

        $roleAfter = Mockery::mock(Role::class)->makePartial();
        $roleAfter->id = 7;
        $roleAfter->name = 'admin';
        $roleAfter->guard_name = 'web';

        $repository->shouldReceive('findById')->once()->with(7)->andReturn($roleBefore);
        $repository->shouldReceive('findByName')->once()->with('admin', 'web')->andReturn(null);
        $repository->shouldReceive('update')->once()->with($roleBefore, $dto)->andReturn(true);
        $roleBefore->shouldReceive('fresh')->once()->andReturn($roleAfter);

        $auditLogService->shouldReceive('logRoleUpdate')->once()->with(
            $actor,
            7,
            ['name' => 'manager', 'guard_name' => 'web'],
            ['name' => 'admin', 'guard_name' => 'web'],
            $request
        );

        $service = new RoleService($repository, $auditLogService);

        $result = $service->update(7, $dto, $actor, $request);

        $this->assertSame($roleAfter, $result);
    }

    public function test_delete_role_deletes_and_logs(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/roles/5', 'DELETE');

        $role = $this->makeRole(id: 5, name: 'editor', guardName: 'web');

        $repository->shouldReceive('findById')->once()->with(5)->andReturn($role);
        $repository->shouldReceive('delete')->once()->with($role)->andReturn(true);
        $auditLogService->shouldReceive('logRoleDeletion')->once()->with($actor, 5, 'editor', $request);

        $service = new RoleService($repository, $auditLogService);

        $deleted = $service->delete(5, $actor, $request);

        $this->assertTrue($deleted);
    }

    public function test_assign_permissions_syncs_and_logs(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/roles/3/permissions', 'POST');

        $role = Mockery::mock(Role::class)->makePartial();
        $role->id = 3;
        $role->name = 'manager';
        $role->guard_name = 'web';

        $freshRole = Mockery::mock(Role::class)->makePartial();
        $freshRole->id = 3;
        $freshRole->name = 'manager';
        $freshRole->guard_name = 'web';

        $repository->shouldReceive('findById')->once()->with(3)->andReturn($role);
        $repository->shouldReceive('assignPermissions')->once()->with($role, [1, 2, 3]);
        $auditLogService->shouldReceive('logPermissionAssignmentToRole')->once()->with($actor, 3, [1, 2, 3], $request);
        $role->shouldReceive('fresh')->once()->andReturn($freshRole);

        $service = new RoleService($repository, $auditLogService);

        $result = $service->assignPermissions(3, [1, 2, 3], $actor, $request);

        $this->assertSame($freshRole, $result);
    }

    public function test_to_response_dto_maps_permissions(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $permission = new Permission();
        $permission->id = 8;
        $permission->name = 'users.view';

        $permissions = new Collection([$permission]);

        $role = new class extends Role
        {
            public Collection $allPermissions;

            public function getAllPermissions(): Collection
            {
                return $this->allPermissions;
            }
        };
        $role->allPermissions = $permissions;
        $role->id = 42;
        $role->name = 'auditor';
        $role->guard_name = 'web';

        $service = new RoleService($repository, $auditLogService);

        $dto = $service->toResponseDTO($role);

        $this->assertSame(42, $dto->id);
        $this->assertSame('auditor', $dto->name);
        $this->assertSame('web', $dto->guardName);
        $this->assertSame([['id' => 8, 'name' => 'users.view']], $dto->permissions);
    }

    private function makeActor(): User
    {
        $actor = new User();
        $actor->id = 1;
        $actor->name = 'Actor';
        $actor->email = 'actor@example.com';

        return $actor;
    }

    private function makeRole(int $id, string $name, string $guardName): Role
    {
        $role = new Role();
        $role->id = $id;
        $role->name = $name;
        $role->guard_name = $guardName;

        return $role;
    }
}
