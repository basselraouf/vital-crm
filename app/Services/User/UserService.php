<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Response;

class UserService
{
    /**
     * Create a new user with assigned roles.
     */
    public function store(array $data)
    {
        try {
            $user = User::create([
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'], // Model cast hashes this automatically
            ]);

            if (isset($data['roles']) && !empty($data['roles'])) {
                $user->assignRole($data['roles']);
            } else {
                $user->assignRole('viewer'); // Optional default fallback
            }

            return Response::successResponse([
                'user_id'  => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'roles'    => $user->getRoleNames(),
            ], 'User created successfully.', 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create user');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create user');
        }
    }

    /**
     * Get a paginated list of all users with their roles.
     */
    public function index()
    {
        try {
            // Eager load roles
            $users = User::with('roles')->paginate(15);

            // Format the response. Since we are not using a dedicated Resource class yet,
            // we will map over the items to format them nicely.
            $users->getCollection()->transform(function ($user) {
                return [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'roles'    => $user->getRoleNames(),
                ];
            });

            return Response::successResponse([
                'users' => $users->items(),
                'meta'  => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch users');
        }
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        try {
            if (\Illuminate\Support\Facades\Auth::id() == $id) {
                return Response::errorResponse("You cannot delete your own account.", [], 403);
            }

            $user = User::findOrFail($id);
            $user->delete();

            return Response::successResponse(null, 'User deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'User');
        } catch (\Exception $e) {
            // This will catch the RuntimeException we threw in the User model if someone tries to delete super_admin
            if ($e instanceof \RuntimeException) {
                return Response::errorResponse($e->getMessage(), [], 403);
            }
            return Response::handleException($e, 'delete user');
        }
    }
}
