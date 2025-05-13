<?php

namespace App\Http\Services\UserProfile;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\UserProfile\UserProfileResource;
use App\Repositories\UserProfile\UserProfileRepository;
use Illuminate\Support\Facades\Response;

class UserProfileService
{
    protected $userProfileRepo;

    public function __construct(UserProfileRepository $userProfileRepo)
    {
        $this->userProfileRepo = $userProfileRepo;
    }

    public function updateUserProfile(array $data)
    {
        try {
            $user = auth()->user();

            $userProfile = $this->userProfileRepo->update($user->id, $data);

            if (!$userProfile) {
                return Response::errorResponse('UserProfile not found', [], 404);
            }

            return Response::successResponse(new AuthResource($userProfile), 'userProfile updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'userProfile');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update userProfile');
        }
    }

}
