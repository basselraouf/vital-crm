<?php

namespace App\Services\Role;

use App\Models\User;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * List all roles with their permissions.
     */
    public function index()
    {
        try {
            $roles = Role::with('permissions')->get()->map(function (Role $role) {
                return [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ];
            });

            return Response::successResponse($roles);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch roles');
        }
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(User $user, string $roleName)
    {
        try {
            if (!Role::where('name', $roleName)->exists()) {
                return Response::errorResponse("Role '{$roleName}' does not exist.", [], 422);
            }

            $user->assignRole($roleName);

            return Response::successResponse([
                'user_id'  => $user->id,
                'username' => $user->username,
                'roles'    => $user->getRoleNames(),
            ], "Role '{$roleName}' assigned successfully.");
        } catch (\Exception $e) {
            return Response::handleException($e, 'assign role');
        }
    }

    /**
     * Revoke a role from a user.
     */
    public function revokeRole(User $user, string $roleName)
    {
        try {
            if (!$user->hasRole($roleName)) {
                return Response::errorResponse("User does not have role '{$roleName}'.", [], 422);
            }

            $user->removeRole($roleName);

            return Response::successResponse([
                'user_id'  => $user->id,
                'username' => $user->username,
                'roles'    => $user->getRoleNames(),
            ], "Role '{$roleName}' revoked successfully.");
        } catch (\Exception $e) {
            return Response::handleException($e, 'revoke role');
        }
    }

    /**
     * Sync permissions on a specific role (replaces all existing permissions).
     */
    public function syncPermissions(Role $role, array $permissions)
    {
        try {
            // Validate all permissions exist
            $existingPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
            $invalid = array_diff($permissions, $existingPermissions);

            if (!empty($invalid)) {
                return Response::errorResponse(
                    'Some permissions do not exist: ' . implode(', ', $invalid),
                    ['invalid_permissions' => $invalid],
                    422
                );
            }

            $role->syncPermissions($permissions);

            return Response::successResponse([
                'role'        => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ], "Permissions updated for role '{$role->name}'.");
        } catch (\Exception $e) {
            return Response::handleException($e, 'sync permissions');
        }
    }
}
