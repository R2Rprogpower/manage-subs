<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Services;

use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Permissions\DTO\CreateRoleDTO;
use App\Modules\Permissions\DTO\RoleResponseDTO;
use App\Modules\Permissions\DTO\UpdateRoleDTO;
use App\Modules\Permissions\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly AuditLogService $auditLogService
    ) {}

    public function create(CreateRoleDTO $dto, ?User $actor = null, ?Request $request = null): Role
    {
        $existing = $this->roleRepository->findByName($dto->name, $dto->guardName);
        if ($existing) {
            throw new \InvalidArgumentException("Role '{$dto->name}' already exists.");
        }

        $role = $this->roleRepository->create($dto);

        if ($actor) {
            $this->auditLogService->logRoleCreation($actor, (int) $role->id, $role->name, $request);
        }

        return $role;
    }

    public function update(int $id, UpdateRoleDTO $dto, ?User $actor = null, ?Request $request = null): Role
    {
        $role = $this->roleRepository->findById($id);
        if (! $role) {
            throw new \InvalidArgumentException("Role with ID {$id} not found.");
        }

        $previousValue = [
            'name' => $role->name,
            'guard_name' => $role->guard_name,
        ];

        if ($dto->name !== null) {
            $existing = $this->roleRepository->findByName($dto->name, $dto->guardName ?? $role->guard_name);
            if ($existing && $existing->id !== $role->id) {
                throw new \InvalidArgumentException("Role '{$dto->name}' already exists.");
            }
        }

        $this->roleRepository->update($role, $dto);
        $role = $role->fresh();

        if ($actor && $role) {
            $newValue = [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ];
            $this->auditLogService->logRoleUpdate($actor, $id, $previousValue, $newValue, $request);
        }

        return $role;
    }

    public function delete(int $id, ?User $actor = null, ?Request $request = null): bool
    {
        $role = $this->roleRepository->findById($id);
        if (! $role) {
            throw new \InvalidArgumentException("Role with ID {$id} not found.");
        }

        $roleName = $role->name;
        $result = $this->roleRepository->delete($role);

        if ($result && $actor) {
            $this->auditLogService->logRoleDeletion($actor, $id, $roleName, $request);
        }

        return $result;
    }

    /**
     * @return Collection<int, Role>
     */
    public function findAll(): Collection
    {
        return $this->roleRepository->findAll();
    }

    public function findById(int $id): ?Role
    {
        return $this->roleRepository->findById($id);
    }

    /**
     * @param  array<int>  $permissionIds
     */
    public function assignPermissions(int $roleId, array $permissionIds, ?User $actor = null, ?Request $request = null): Role
    {
        $role = $this->roleRepository->findById($roleId);
        if (! $role) {
            throw new \InvalidArgumentException("Role with ID {$roleId} not found.");
        }

        $this->roleRepository->assignPermissions($role, $permissionIds);

        if ($actor) {
            $this->auditLogService->logPermissionAssignmentToRole($actor, $roleId, $permissionIds, $request);
        }

        return $role->fresh();
    }

    public function assignRoleToUser(int $userId, string $roleName, ?User $actor = null, ?Request $request = null): void
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);
        if (! $user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found.");
        }

        $role = $this->roleRepository->findByName($roleName);
        if (! $role) {
            throw new \InvalidArgumentException("Role '{$roleName}' not found.");
        }

        $user->assignRole($role);

        if ($actor) {
            $this->auditLogService->logRoleAssignment($actor, $user, $roleName, $request);
        }
    }

    public function removeRoleFromUser(int $userId, string $roleName, ?User $actor = null, ?Request $request = null): void
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);
        if (! $user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found.");
        }

        $user->removeRole($roleName);

        if ($actor) {
            $this->auditLogService->logRoleRemoval($actor, $user, $roleName, $request);
        }
    }

    public function toResponseDTO(Role $role): RoleResponseDTO
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions */
        $permissions = $role->getAllPermissions();

        return new RoleResponseDTO(
            id: (int) $role->id,
            name: $role->name,
            guardName: $role->guard_name,
            permissions: $permissions->map(fn ($p) => [
                'id' => (int) $p->id,
                'name' => $p->name,
            ])->toArray()
        );
    }
}
