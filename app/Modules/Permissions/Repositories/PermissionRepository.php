<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class PermissionRepository
{
    public function findById(int $id): ?Permission
    {
        /** @var Permission|null */
        return Permission::query()->find($id);
    }

    /**
     * @return Collection<int, Permission>
     */
    public function findAll(): Collection
    {
        return Permission::all();
    }

    public function findByName(string $name, string $guardName = 'web'): ?Permission
    {
        /** @var Permission|null */
        return Permission::query()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    public function create(string $name, string $guardName = 'web'): Permission
    {
        /** @var Permission */
        return Permission::create([
            'name' => $name,
            'guard_name' => $guardName,
        ]);
    }

    public function update(Permission $permission, string $name): bool
    {
        return $permission->update(['name' => $name]);
    }

    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }
}
