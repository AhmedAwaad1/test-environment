<?php

namespace App\Repositories\UserRepository;

use App\Models\User;

class UserRepository
{
    public function getOrCreateGuestUser($data)
    {
        $phone = $data['phone'];
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $baseEmail = $data['email'] ?? $data['name'] . '@guest.com';
            $email = $baseEmail;
            $counter = 0;

            while (User::where('email', $email)->exists()) {
                $counter++;
                $email = $data['name'] . $counter . '@guest.com';
            }

            $user = User::create([
                'username' => $data['name'],
                'phone'    => $phone,
                'email'    => $email,
                'password' => bcrypt('12345678'),
                'type'     => 'user',
            ]);
        }

        return $user;
    }


}
