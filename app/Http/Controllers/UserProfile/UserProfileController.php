<?php

namespace App\Http\Controllers\UserProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserProfile\UserProfileRequest;
use App\Http\Services\UserProfile\UserProfileService;

class UserProfileController extends Controller
{
    public $userProfileService;
    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function update(UserProfileRequest $request)
    {
        return $this->userProfileService->updateUserProfile($request->validated());
    }

}
