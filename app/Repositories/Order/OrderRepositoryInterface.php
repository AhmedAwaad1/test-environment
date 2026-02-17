<?php

namespace App\Repositories\Order;

interface OrderRepositoryInterface
{
    public function getAllUserOrder($request);
    public function findOrderById($id);
    public function createOrder(array $data, $cart);
    public function find($id, array $columns = ['*'], array $relations = []): ?\Illuminate\Database\Eloquent\Model;
    public function create(array $data): \Illuminate\Database\Eloquent\Model;
    public function update($id, array $data);
    public function delete($id): ?bool;
    public function getCartForOrder($userId);
}
