<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Repositories;

use App\Modules\Permissions\DTO\CreateRoleDTO;
use App\Modules\Permissions\DTO\UpdateRoleDTO;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    public function findById(int $id): ?Role
    {
        /** @var Role|null */
        return Role::query()->find($id);
    }

    /**
     * @return Collection<int, Role>
     */
    public function findAll(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function findByName(string $name, string $guardName = 'web'): ?Role
    {
        /** @var Role|null */
        return Role::query()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    public function create(CreateRoleDTO $dto): Role
    {
        /** @var Role */
        return Role::create($dto->toArray());
    }

    public function update(Role $role, UpdateRoleDTO $dto): bool
    {
        return $role->update($dto->toArray());
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * @param  array<int>  $permissionIds
     */
    public function assignPermissions(Role $role, array $permissionIds): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions */
        $permissions = \Spatie\Permission\Models\Permission::query()->whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);
    }

    /**
     * @return Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getPermissions(Role $role): Collection
    {
        /** @var Collection<int, \Spatie\Permission\Models\Permission> */
        return $role->getAllPermissions();
    }
}
