<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log a role assignment action
     */
    public function logRoleAssignment(
        User $actor,
        User $targetUser,
        string $roleName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'role_assigned',
            targetType: 'user',
            targetId: $targetUser->id,
            newValue: ['role' => $roleName],
            request: $request
        );
    }

    /**
     * Log a role removal action
     */
    public function logRoleRemoval(
        User $actor,
        User $targetUser,
        string $roleName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'role_removed',
            targetType: 'user',
            targetId: $targetUser->id,
            previousValue: ['role' => $roleName],
            request: $request
        );
    }

    /**
     * Log a permission assignment to user
     */
    public function logPermissionAssignmentToUser(
        User $actor,
        User $targetUser,
        string $permissionName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'permission_assigned_to_user',
            targetType: 'user',
            targetId: $targetUser->id,
            newValue: ['permission' => $permissionName],
            request: $request
        );
    }

    /**
     * Log a permission assignment to role
     *
     * @param  array<int>  $permissionIds
     */
    public function logPermissionAssignmentToRole(
        User $actor,
        int $roleId,
        array $permissionIds,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'permission_assigned_to_role',
            targetType: 'role',
            targetId: $roleId,
            newValue: ['permission_ids' => $permissionIds],
            request: $request
        );
    }

    /**
     * Log role creation
     */
    public function logRoleCreation(
        User $actor,
        int $roleId,
        string $roleName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'role_created',
            targetType: 'role',
            targetId: $roleId,
            newValue: ['name' => $roleName],
            request: $request
        );
    }

    /**
     * Log role update
     *
     * @param  array<string, mixed>  $previousValue
     * @param  array<string, mixed>  $newValue
     */
    public function logRoleUpdate(
        User $actor,
        int $roleId,
        array $previousValue,
        array $newValue,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'role_updated',
            targetType: 'role',
            targetId: $roleId,
            previousValue: $previousValue,
            newValue: $newValue,
            request: $request
        );
    }

    /**
     * Log role deletion
     */
    public function logRoleDeletion(
        User $actor,
        int $roleId,
        string $roleName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'role_deleted',
            targetType: 'role',
            targetId: $roleId,
            previousValue: ['name' => $roleName],
            request: $request
        );
    }

    /**
     * Log permission creation
     */
    public function logPermissionCreation(
        User $actor,
        int $permissionId,
        string $permissionName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'permission_created',
            targetType: 'permission',
            targetId: $permissionId,
            newValue: ['name' => $permissionName],
            request: $request
        );
    }

    /**
     * Log permission update
     *
     * @param  array<string, mixed>  $previousValue
     * @param  array<string, mixed>  $newValue
     */
    public function logPermissionUpdate(
        User $actor,
        int $permissionId,
        array $previousValue,
        array $newValue,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'permission_updated',
            targetType: 'permission',
            targetId: $permissionId,
            previousValue: $previousValue,
            newValue: $newValue,
            request: $request
        );
    }

    /**
     * Log permission deletion
     */
    public function logPermissionDeletion(
        User $actor,
        int $permissionId,
        string $permissionName,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'permission_deleted',
            targetType: 'permission',
            targetId: $permissionId,
            previousValue: ['name' => $permissionName],
            request: $request
        );
    }

    /**
     * Log user creation
     */
    public function logUserCreation(
        User $actor,
        int $userId,
        string $userEmail,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'user_created',
            targetType: 'user',
            targetId: $userId,
            newValue: ['email' => $userEmail],
            request: $request
        );
    }

    /**
     * Log user update
     *
     * @param  array<string, mixed>  $previousValue
     * @param  array<string, mixed>  $newValue
     */
    public function logUserUpdate(
        User $actor,
        int $userId,
        array $previousValue,
        array $newValue,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'user_updated',
            targetType: 'user',
            targetId: $userId,
            previousValue: $previousValue,
            newValue: $newValue,
            request: $request
        );
    }

    /**
     * Log user deletion
     */
    public function logUserDeletion(
        User $actor,
        int $userId,
        string $userEmail,
        ?Request $request = null
    ): AuditLog {
        return $this->createLog(
            actor: $actor,
            actionType: 'user_deleted',
            targetType: 'user',
            targetId: $userId,
            previousValue: ['email' => $userEmail],
            request: $request
        );
    }

    /**
     * Log authentication login
     */
    public function logAuthLogin(User $actor, ?Request $request = null): AuditLog
    {
        return $this->createLog(
            actor: $actor,
            actionType: 'auth_login',
            targetType: 'user',
            targetId: $actor->id,
            request: $request
        );
    }

    /**
     * Log authentication logout
     */
    public function logAuthLogout(User $actor, ?Request $request = null): AuditLog
    {
        return $this->createLog(
            actor: $actor,
            actionType: 'auth_logout',
            targetType: 'user',
            targetId: $actor->id,
            request: $request
        );
    }

    /**
     * Log MFA setup
     */
    public function logMfaSetup(User $actor, ?Request $request = null): AuditLog
    {
        return $this->createLog(
            actor: $actor,
            actionType: 'mfa_setup',
            targetType: 'user',
            targetId: $actor->id,
            request: $request
        );
    }

    /**
     * Log MFA verified
     */
    public function logMfaVerified(User $actor, ?Request $request = null): AuditLog
    {
        return $this->createLog(
            actor: $actor,
            actionType: 'mfa_verified',
            targetType: 'user',
            targetId: $actor->id,
            request: $request
        );
    }

    /**
     * Log MFA verification failure
     */
    public function logMfaVerificationFailure(User $actor, ?Request $request = null): AuditLog
    {
        return $this->createLog(
            actor: $actor,
            actionType: 'mfa_verification_failed',
            targetType: 'user',
            targetId: $actor->id,
            request: $request
        );
    }

    /**
     * Log token revoked
     */
    public function logTokenRevoked(User $actor, ?Request $request = null, ?int $tokenId = null): AuditLog
    {
        $metadata = $tokenId !== null ? ['token_id' => $tokenId] : null;

        return $this->createLog(
            actor: $actor,
            actionType: 'token_revoked',
            targetType: 'user',
            targetId: $actor->id,
            request: $request,
            metadata: $metadata
        );
    }

    /**
     * Create an audit log entry
     *
     * @param  array<string, mixed>|null  $previousValue
     * @param  array<string, mixed>|null  $newValue
     * @param  array<string, mixed>|null  $metadata
     */
    private function createLog(
        User $actor,
        string $actionType,
        string $targetType,
        int $targetId,
        ?array $previousValue = null,
        ?array $newValue = null,
        ?Request $request = null,
        ?array $metadata = null
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actor->id,
            'ip_address' => $request?->ip(),
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'previous_value' => $previousValue,
            'new_value' => $newValue,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
