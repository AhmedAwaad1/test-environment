<?php

namespace App\Actions\Order;

use App\Repositories\UserRepository\UserRepository;
use App\Repositories\UserRepository\UserRepositoryInterface;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Cart\CartRepositoryInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResolveUserAction
{
    public function __construct(
        protected UserRepositoryInterface $userRepo,
        protected CartRepositoryInterface $cartRepo
    ) {}

    public function execute(array $request): array
    {
        $user = auth()->user();
        $isGuest = false;
        $token = null;

        if (!$user) {
            $user = $this->userRepo->getOrCreateGuestUser($request);
            $isGuest = true;

            if (!empty($request['session_id'])) {
                $guestCart = $this->cartRepo->findBySessionId($request['session_id']);
                if ($guestCart) {
                    $guestCart->update([
                        'user_id'    => $user->id,
                        'session_id' => null,
                    ]);
                }
            }

            $token = JWTAuth::fromUser($user);
        }

        return [
            'user' => $user,
            'token' => $token,
            'is_guest' => $isGuest
        ];
    }
}
