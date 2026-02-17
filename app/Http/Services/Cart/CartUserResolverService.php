<?php

namespace App\Http\Services\Cart;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartUserResolverService
{
    /**
     * Resolve user from the current request.
     */
    public function resolveUserFromRequest(): ?User
    {
        // If user is already authenticated via session/guard
        if (auth()->check()) {
            return auth()->user();
        }

        // Check for Bearer token in the header
        $token = request()->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            return JWTAuth::setToken($token)->authenticate();
        } catch (\Throwable $e) {
            return null; // Treat as guest on token error
        }
    }
}
