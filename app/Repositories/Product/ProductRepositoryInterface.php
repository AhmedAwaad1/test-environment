<?php

namespace App\Repositories\Product;

use App\Models\Product;

interface ProductRepositoryInterface
{
    public function getAll(array $filters = []);
    public function find($id, array $columns = ['*'], array $relations = []): ?\Illuminate\Database\Eloquent\Model;
    public function findWithVariants($id);
    public function create(array $data): \Illuminate\Database\Eloquent\Model;
    public function update($id, array $data);
    public function delete($id): ?bool;
    public function updateProductPrices(Product $product, array $prices): void;
    public function createProductPrices(Product $product, array $prices): void;
}
