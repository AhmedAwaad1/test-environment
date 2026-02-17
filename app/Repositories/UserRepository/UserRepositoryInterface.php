<?php

namespace App\Repositories\UserRepository;

interface UserRepositoryInterface
{
    public function getOrCreateGuestUser($data);
    public function find($id, array $columns = ['*'], array $relations = []): ?\Illuminate\Database\Eloquent\Model;
    public function create(array $data): \Illuminate\Database\Eloquent\Model;
    public function update($id, array $data);
    public function delete($id): ?bool;
    public function findByEmail(string $email);
}
