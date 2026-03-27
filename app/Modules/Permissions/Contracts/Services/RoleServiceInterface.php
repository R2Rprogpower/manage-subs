<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Contracts\Services;

use App\Models\User;
use App\Modules\Permissions\DTO\CreateRoleDTO;
use App\Modules\Permissions\DTO\RoleResponseDTO;
use App\Modules\Permissions\DTO\UpdateRoleDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

interface RoleServiceInterface
{
    public function create(CreateRoleDTO $dto, ?User $actor = null, ?Request $request = null): Role;

    public function update(int $id, UpdateRoleDTO $dto, ?User $actor = null, ?Request $request = null): Role;

    public function delete(int $id, ?User $actor = null, ?Request $request = null): bool;

    /**
     * @return Collection<int, Role>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?Role;

    /**
     * @param  array<int>  $permissionIds
     */
    public function assignPermissions(int $roleId, array $permissionIds, ?User $actor = null, ?Request $request = null): Role;

    public function assignRoleToUser(int $userId, string $roleName, ?User $actor = null, ?Request $request = null): void;

    public function removeRoleFromUser(int $userId, string $roleName, ?User $actor = null, ?Request $request = null): void;

    public function toResponseDTO(Role $role): RoleResponseDTO;
}
