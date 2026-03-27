<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Contracts\Services;

use App\Models\User;
use App\Modules\Permissions\DTO\PermissionResponseDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

interface PermissionServiceInterface
{
    public function create(string $name, string $guardName = 'web', ?User $actor = null, ?Request $request = null): Permission;

    public function update(int $id, string $name, ?User $actor = null, ?Request $request = null): Permission;

    public function delete(int $id, ?User $actor = null, ?Request $request = null): bool;

    /**
     * @return Collection<int, Permission>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?Permission;

    public function assignToUser(int $userId, int $permissionId, ?User $actor = null, ?Request $request = null): void;

    public function assignToRole(int $roleId, int $permissionId, ?User $actor = null, ?Request $request = null): void;

    public function removeFromUser(int $userId, int $permissionId): void;

    public function toResponseDTO(Permission $permission): PermissionResponseDTO;
}
