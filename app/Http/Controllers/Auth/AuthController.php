<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public $authService;

    public function __construct(AuthService $authService)
    {
        $this->middleware('auth:api')
             ->except('logout', 'register', 'login');
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request);
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request);
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function refresh(Request $request)
    {
        $oldToken = JWTAuth::getToken();
        if (!$oldToken) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        return $this->authService->refreshToken($oldToken);
    }

}
