<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Contracts\Repositories;

use App\Modules\Permissions\DTO\CreateRoleDTO;
use App\Modules\Permissions\DTO\UpdateRoleDTO;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    /**
     * @return Collection<int, Role>
     */
    public function findAll(): Collection;

    public function findByName(string $name, string $guardName = 'web'): ?Role;

    public function create(CreateRoleDTO $dto): Role;

    public function update(Role $role, UpdateRoleDTO $dto): bool;

    public function delete(Role $role): bool;

    /**
     * @param  array<int>  $permissionIds
     */
    public function assignPermissions(Role $role, array $permissionIds): void;

    /**
     * @return Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getPermissions(Role $role): Collection;
}
