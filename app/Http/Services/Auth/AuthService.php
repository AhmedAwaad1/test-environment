<?php

namespace App\Http\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register($request)
    {
        try{
            $request->merge([
                'password' => bcrypt($request->password),
            ]);
            $user  = User::create($request->only([
                'username', 'type', 'email', 'password', 'phone'
            ]));
            $token = JWTAuth::fromUser($user);

            $responseData          = $user->toArray();
            $responseData['token'] = $token;

            return Response::successResponse($responseData, 'User created successfully', 201);
        }
        catch(\Exception $e){
            //duplicate entry
            if($e->getCode() === '23000'){
                return Response::errorResponse('User with these credentials already exists', [], 400);
            }
            return Response::errorResponse('Failed to create user: ' . $e->getMessage(), [], 400);
        }

    }

    public function login($request)
    {
        // dd($request->all());
        $user = User::where('email', $request->email)
                    ->first();

        if (!$user) {
            return Response::errorResponse('User is Not Found', [], 400);
        }

        if (!$user->validatePassportPassword($request->password)) {
            return Response::errorResponse('Password is Incorrect', [], 400);
        }

        $token = JWTAuth::fromUser($user);

        $responseData          = $user->toArray();
        $responseData['token'] = $token;

        return Response::successResponse($responseData);
    }

    public function logout()
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            JWTAuth::invalidate(JWTAuth::getToken());

            return Response::successResponse('Logged out successfully', [], 200);
        }

        return Response::errorResponse('User not authenticated', [], 401);
    }

    public function refreshToken($oldToken)
    {
        try {
            JWTAuth::setToken($oldToken);
            $newAccessToken = JWTAuth::refresh();

            return Response::successResponse([
                'new_token' => $newAccessToken
            ], 200);
        } catch (\Exception $e) {
            return Response::errorResponse('Invalid refresh token', [], 400);
        }
    }

}
