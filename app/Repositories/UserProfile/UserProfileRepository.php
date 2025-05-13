<?php

namespace App\Repositories\UserProfile;

use App\Models\User;

class UserProfileRepository
{
    public function find($userId)
    {
        return User::where('id', $userId)->first();
    }

    public function update($userId, array $data)
    {
        $userProfile = $this->find($userId);

        if ($userProfile) {
            $userProfile->update($data);
        }
        return $userProfile;
    }
}
