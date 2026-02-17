<?php

namespace App\Repositories\Cart;

use App\Models\Cart;

interface CartRepositoryInterface
{
    public function findOrCreateUserCart($userId);
    public function findUserCart($userId);
    public function findOrCreateBySessionId($sessionId);
    public function findBySessionId($sessionId);
    public function find($id, array $columns = ['*'], array $relations = []): ?\Illuminate\Database\Eloquent\Model;
    public function create(array $data): \Illuminate\Database\Eloquent\Model;
    public function update($id, array $data);
    public function delete($id): ?bool;
}
