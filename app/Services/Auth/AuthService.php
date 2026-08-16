<?php

namespace App\Services\Auth;

use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return Response::errorResponse('User not found', [], 404);
        }

        if (!$user->validatePassportPassword($request->password)) {
            return Response::errorResponse('Password is incorrect', [], 401);
        }

        $token = JWTAuth::fromUser($user);

        $responseData          = (new AuthResource($user))->resolve();
        $responseData['token'] = $token;

        return Response::successResponse($responseData);
    }

    public function logout()
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            JWTAuth::invalidate(JWTAuth::getToken());
            return Response::successResponse(null, 'Logged out successfully');
        }

        return Response::errorResponse('User not authenticated', [], 401);
    }

    public function refreshToken($oldToken)
    {
        try {
            JWTAuth::setToken($oldToken);
            $newAccessToken = JWTAuth::refresh();

            return Response::successResponse(['new_token' => $newAccessToken], null, 200);
        } catch (\Exception $e) {
            return Response::errorResponse('Invalid or expired token', [], 401);
        }
    }
}
