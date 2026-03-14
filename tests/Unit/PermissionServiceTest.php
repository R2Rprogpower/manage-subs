<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Permissions\Repositories\PermissionRepository;
use App\Modules\Permissions\Services\PermissionService;
use Illuminate\Http\Request;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_permission_creates_and_logs_when_actor_present(): void
    {
        $repository = Mockery::mock(PermissionRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/permissions', 'POST');

        $permission = $this->makePermission(id: 10, name: 'users.create', guardName: 'web');

        $repository->shouldReceive('findByName')->once()->with('users.create', 'web')->andReturn(null);
        $repository->shouldReceive('create')->once()->with('users.create', 'web')->andReturn($permission);
        $auditLogService->shouldReceive('logPermissionCreation')->once()->with($actor, 10, 'users.create', $request);

        $service = new PermissionService($repository, $auditLogService);

        $result = $service->create('users.create', 'web', $actor, $request);

        $this->assertSame($permission, $result);
    }

    public function test_create_permission_throws_when_duplicate_exists(): void
    {
        $repository = Mockery::mock(PermissionRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $existingPermission = $this->makePermission(id: 11, name: 'users.create', guardName: 'web');
        $repository->shouldReceive('findByName')->once()->with('users.create', 'web')->andReturn($existingPermission);
        $repository->shouldNotReceive('create');

        $service = new PermissionService($repository, $auditLogService);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Permission 'users.create' already exists.");

        $service->create('users.create');
    }

    public function test_update_permission_updates_and_logs(): void
    {
        $repository = Mockery::mock(PermissionRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/permissions/7', 'PATCH');

        $permissionBefore = Mockery::mock(Permission::class)->makePartial();
        $permissionBefore->id = 7;
        $permissionBefore->name = 'users.old';
        $permissionBefore->guard_name = 'web';

        $permissionAfter = Mockery::mock(Permission::class)->makePartial();
        $permissionAfter->id = 7;
        $permissionAfter->name = 'users.new';
        $permissionAfter->guard_name = 'web';

        $repository->shouldReceive('findById')->once()->with(7)->andReturn($permissionBefore);
        $repository->shouldReceive('findByName')->once()->with('users.new', 'web')->andReturn(null);
        $repository->shouldReceive('update')->once()->with($permissionBefore, 'users.new')->andReturn(true);
        $permissionBefore->shouldReceive('fresh')->once()->andReturn($permissionAfter);
        $auditLogService->shouldReceive('logPermissionUpdate')->once()->with(
            $actor,
            7,
            ['name' => 'users.old'],
            ['name' => 'users.new'],
            $request
        );

        $service = new PermissionService($repository, $auditLogService);

        $result = $service->update(7, 'users.new', $actor, $request);

        $this->assertSame($permissionAfter, $result);
    }

    public function test_delete_permission_deletes_and_logs(): void
    {
        $repository = Mockery::mock(PermissionRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $actor = $this->makeActor();
        $request = Request::create('/api/permissions/5', 'DELETE');

        $permission = $this->makePermission(id: 5, name: 'users.delete', guardName: 'web');

        $repository->shouldReceive('findById')->once()->with(5)->andReturn($permission);
        $repository->shouldReceive('delete')->once()->with($permission)->andReturn(true);
        $auditLogService->shouldReceive('logPermissionDeletion')->once()->with($actor, 5, 'users.delete', $request);

        $service = new PermissionService($repository, $auditLogService);

        $deleted = $service->delete(5, $actor, $request);

        $this->assertTrue($deleted);
    }

    public function test_to_response_dto_maps_permission_values(): void
    {
        $repository = Mockery::mock(PermissionRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $permission = $this->makePermission(id: 99, name: 'roles.assign', guardName: 'web');

        $service = new PermissionService($repository, $auditLogService);

        $dto = $service->toResponseDTO($permission);

        $this->assertSame(99, $dto->id);
        $this->assertSame('roles.assign', $dto->name);
        $this->assertSame('web', $dto->guardName);
    }

    private function makeActor(): User
    {
        $actor = new User();
        $actor->id = 1;
        $actor->name = 'Actor';
        $actor->email = 'actor@example.com';

        return $actor;
    }

    private function makePermission(int $id, string $name, string $guardName): Permission
    {
        $permission = new Permission();
        $permission->id = $id;
        $permission->name = $name;
        $permission->guard_name = $guardName;

        return $permission;
    }
}
