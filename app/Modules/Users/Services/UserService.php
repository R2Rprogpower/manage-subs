<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Users\DTO\CreateUserDTO;
use App\Modules\Users\DTO\UpdateUserDTO;
use App\Modules\Users\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function findAll(): Collection
    {
        return $this->userRepository->findAll();
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(CreateUserDTO $dto, ?User $actor = null, ?Request $request = null): User
    {
        $existing = $this->userRepository->findByEmail($dto->email);
        if ($existing) {
            throw new \InvalidArgumentException("User with email '{$dto->email}' already exists.");
        }

        $user = $this->userRepository->create($dto);

        if ($actor) {
            $this->auditLogService->logUserCreation($actor, (int) $user->id, $user->email, $request);
        }

        return $user;
    }

    public function update(int $id, UpdateUserDTO $dto, ?User $actor = null, ?Request $request = null): User
    {
        $user = $this->userRepository->findById($id);
        if (! $user) {
            throw new \InvalidArgumentException("User with ID {$id} not found.");
        }

        $previousValue = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        if ($dto->email !== null) {
            $existing = $this->userRepository->findByEmail($dto->email);
            if ($existing && $existing->id !== $user->id) {
                throw new \InvalidArgumentException("User with email '{$dto->email}' already exists.");
            }
        }

        $updateDto = $dto;
        if ($dto->password !== null) {
            $updateDto = new UpdateUserDTO(
                name: $dto->name,
                email: $dto->email,
                password: bcrypt($dto->password)
            );
        }

        $this->userRepository->update($user, $updateDto);
        $user->refresh();
        $user->load('roles', 'permissions');

        $newValue = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        if ($actor) {
            $this->auditLogService->logUserUpdate(
                $actor,
                (int) $user->id,
                $previousValue,
                $newValue,
                $request
            );
        }

        return $user;
    }

    public function delete(int $id, ?User $actor = null, ?Request $request = null): void
    {
        $user = $this->userRepository->findById($id);
        if (! $user) {
            throw new \InvalidArgumentException("User with ID {$id} not found.");
        }

        $userEmail = $user->email;
        $this->userRepository->delete($user);

        if ($actor) {
            $this->auditLogService->logUserDeletion($actor, (int) $id, $userEmail, $request);
        }
    }
}
