<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Services;

use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Permissions\DTO\PermissionResponseDTO;
use App\Modules\Permissions\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly AuditLogService $auditLogService
    ) {}

    public function create(string $name, string $guardName = 'web', ?User $actor = null, ?Request $request = null): Permission
    {
        $existing = $this->permissionRepository->findByName($name, $guardName);
        if ($existing) {
            throw new \InvalidArgumentException("Permission '{$name}' already exists.");
        }

        $permission = $this->permissionRepository->create($name, $guardName);

        if ($actor) {
            $this->auditLogService->logPermissionCreation($actor, (int) $permission->id, $permission->name, $request);
        }

        return $permission;
    }

    public function update(int $id, string $name, ?User $actor = null, ?Request $request = null): Permission
    {
        $permission = $this->permissionRepository->findById($id);
        if (! $permission) {
            throw new \InvalidArgumentException("Permission with ID {$id} not found.");
        }

        $previousValue = ['name' => $permission->name];

        $existing = $this->permissionRepository->findByName($name, $permission->guard_name);
        if ($existing && $existing->id !== $permission->id) {
            throw new \InvalidArgumentException("Permission '{$name}' already exists.");
        }

        $this->permissionRepository->update($permission, $name);
        $permission = $permission->fresh();

        if ($actor && $permission) {
            $newValue = ['name' => $permission->name];
            $this->auditLogService->logPermissionUpdate($actor, $id, $previousValue, $newValue, $request);
        }

        return $permission;
    }

    public function delete(int $id, ?User $actor = null, ?Request $request = null): bool
    {
        $permission = $this->permissionRepository->findById($id);
        if (! $permission) {
            throw new \InvalidArgumentException("Permission with ID {$id} not found.");
        }

        $permissionName = $permission->name;
        $result = $this->permissionRepository->delete($permission);

        if ($result && $actor) {
            $this->auditLogService->logPermissionDeletion($actor, $id, $permissionName, $request);
        }

        return $result;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function findAll(): Collection
    {
        return $this->permissionRepository->findAll();
    }

    public function findById(int $id): ?Permission
    {
        return $this->permissionRepository->findById($id);
    }

    public function assignToUser(int $userId, int $permissionId, ?User $actor = null, ?Request $request = null): void
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);
        if (! $user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found.");
        }

        $permission = $this->permissionRepository->findById($permissionId);
        if (! $permission) {
            throw new \InvalidArgumentException("Permission with ID {$permissionId} not found.");
        }

        $user->givePermissionTo($permission);

        if ($actor) {
            $this->auditLogService->logPermissionAssignmentToUser($actor, $user, $permission->name, $request);
        }
    }

    public function assignToRole(int $roleId, int $permissionId, ?User $actor = null, ?Request $request = null): void
    {
        /** @var Role|null $role */
        $role = Role::query()->find($roleId);
        if (! $role) {
            throw new \InvalidArgumentException("Role with ID {$roleId} not found.");
        }

        $permission = $this->permissionRepository->findById($permissionId);
        if (! $permission) {
            throw new \InvalidArgumentException("Permission with ID {$permissionId} not found.");
        }

        $role->givePermissionTo($permission);

        if ($actor) {
            $this->auditLogService->logPermissionAssignmentToRole($actor, $roleId, [$permissionId], $request);
        }
    }

    public function removeFromUser(int $userId, int $permissionId): void
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);
        if (! $user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found.");
        }

        $permission = $this->permissionRepository->findById($permissionId);
        if (! $permission) {
            throw new \InvalidArgumentException("Permission with ID {$permissionId} not found.");
        }

        $user->revokePermissionTo($permission);
    }

    public function toResponseDTO(Permission $permission): PermissionResponseDTO
    {
        return new PermissionResponseDTO(
            id: (int) $permission->id,
            name: $permission->name,
            guardName: $permission->guard_name
        );
    }
}
