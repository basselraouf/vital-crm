<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Services\User\UserService;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create a new user (with assigned roles).
     */
    public function store(UserRequest $request)
    {
        return $this->userService->store($request->validated());
    }

    /**
     * Get all users.
     * GET /api/users
     */
    public function index()
    {
        return $this->userService->index();
    }

    /**
     * Delete a user.
     * DELETE /api/users/{user}
     */
    public function destroy($id)
    {
        return $this->userService->destroy($id);
    }
}
