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
     * List all available permissions.
     */
    public function getPermissions()
    {
        try {
            $permissions = Permission::all()->map(function (Permission $permission) {
                return [
                    'id'   => $permission->id,
                    'name' => $permission->name,
                ];
            });

            return Response::successResponse($permissions);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch permissions');
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
            if ($user->isSuperAdmin() && $roleName === 'super_admin') {
                return Response::errorResponse("Cannot revoke the super_admin role from the Super Admin account.", [], 403);
            }

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
     * Create a new custom role.
     */
    public function createRole(string $name)
    {
        try {
            $name = strtolower(trim($name));

            if (Role::where('name', $name)->exists()) {
                return Response::errorResponse("Role '{$name}' already exists.", [], 422);
            }

            $role = Role::create(['name' => $name, 'guard_name' => 'api']);

            return Response::successResponse([
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => [],
            ], "Role '{$name}' created successfully.", 201);
        } catch (\Exception $e) {
            return Response::handleException($e, 'create role');
        }
    }

    /**
     * Delete a custom role (cannot delete system roles).
     */
    public function deleteRole(Role $role)
    {
        try {
            $protected = ['super_admin', 'admin', 'agent'];

            if (in_array($role->name, $protected)) {
                return Response::errorResponse("The '{$role->name}' role is a system role and cannot be deleted.", [], 403);
            }

            $role->delete();

            return Response::successResponse(null, "Role '{$role->name}' deleted successfully.");
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete role');
        }
    }

    /**
     * Sync permissions on a specific role (replaces all existing permissions).
     */
    public function syncPermissions(Role $role, array $permissions)
    {
        try {
            if ($role->name === 'super_admin') {
                return Response::errorResponse("The super_admin role permissions cannot be modified.", [], 403);
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
