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
}
