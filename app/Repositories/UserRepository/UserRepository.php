<?php

namespace App\Repositories\UserRepository;

use App\Models\User;

use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function getOrCreateGuestUser($data)
    {
        $phone = $data['phone'];
        $user = $this->model->where('phone', $phone)->first();

        if (!$user) {
            $baseEmail = $data['email'] ?? $data['name'] . '@guest.com';
            $email = $baseEmail;
            $counter = 0;

            while ($this->model->where('email', $email)->exists()) {
                $counter++;
                $email = $data['name'] . $counter . '@guest.com';
            }

            $user = $this->model->create([
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
