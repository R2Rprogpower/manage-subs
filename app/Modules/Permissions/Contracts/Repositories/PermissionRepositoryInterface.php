<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission;

    /**
     * @return Collection<int, Permission>
     */
    public function findAll(): Collection;

    public function findByName(string $name, string $guardName = 'web'): ?Permission;

    public function create(string $name, string $guardName = 'web'): Permission;

    public function update(Permission $permission, string $name): bool;

    public function delete(Permission $permission): bool;
}
