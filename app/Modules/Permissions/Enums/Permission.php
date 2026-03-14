<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Enums;

enum Permission: string
{
    // Role management permissions
    case MANAGE_ROLES = 'manage-roles';
    case VIEW_ROLES = 'view-roles';
    case CREATE_ROLES = 'create-roles';
    case UPDATE_ROLES = 'update-roles';
    case DELETE_ROLES = 'delete-roles';
    case ASSIGN_ROLES = 'assign-roles';

    // Permission management permissions
    case MANAGE_PERMISSIONS = 'manage-permissions';
    case VIEW_PERMISSIONS = 'view-permissions';
    case CREATE_PERMISSIONS = 'create-permissions';
    case UPDATE_PERMISSIONS = 'update-permissions';
    case DELETE_PERMISSIONS = 'delete-permissions';
    case ASSIGN_PERMISSIONS = 'assign-permissions';

    // User management permissions
    case MANAGE_USERS = 'manage-users';
    case VIEW_USERS = 'view-users';
    case CREATE_USERS = 'create-users';
    case UPDATE_USERS = 'update-users';
    case DELETE_USERS = 'delete-users';

    /**
     * Get all permission values as array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get permission names as array
     *
     * @return array<string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
