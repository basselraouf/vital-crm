<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AssignRoleRequest;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Models\User;
use App\Services\Role\RoleService;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * List all roles with their permissions.
     * GET /api/roles
     */
    public function index()
    {
        return $this->roleService->index();
    }

    /**
     * List all available permissions.
     * GET /api/permissions
     */
    public function permissions()
    {
        return $this->roleService->getPermissions();
    }

    /**
     * Assign a role to a user.
     * POST /api/users/{user}/roles
     */
    public function assignRole(AssignRoleRequest $request, User $user)
    {
        return $this->roleService->assignRole($user, $request->role);
    }

    /**
     * Revoke a role from a user.
     * DELETE /api/users/{user}/roles/{role}
     */
    public function revokeRole(User $user, string $role)
    {
        return $this->roleService->revokeRole($user, $role);
    }

    /**
     * Sync permissions on a role (replaces all existing permissions).
     * PUT /api/roles/{role}/permissions
     */
    public function syncPermissions(SyncPermissionsRequest $request, Role $role)
    {
        return $this->roleService->syncPermissions($role, $request->permissions);
    }
}
