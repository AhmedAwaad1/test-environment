<?php

namespace App\Repositories\Favorite;

use App\Models\Favorite;
use Illuminate\Support\Facades\Storage;

class FavoriteRepository
{
    public function getAll()
    {
        return Favorite::where('user_id', auth()->id())
            ->with(['product.images', 'productSetItem.products.productPrices.currency', 'productSetItem.products.images']);
    }

    public function getByUser($userId)
    {
        return Favorite::where('user_id', $userId)
            ->with(['product.images', 'productSetItem.products.productPrices.currency', 'productSetItem.products.images']);
    }

    public function find($id)
    {
        return Favorite::where('user_id', auth()->id())
        ->where('id', $id)
        ->with(['product.images', 'user', 'productSetItem.products.productPrices.currency', 'productSetItem.products.images'])
        ->find($id);
    }

    public function create(array $data)
    {
        $query = Favorite::where('user_id', $data['user_id']);

        if (isset($data['product_id'])) {
            $query->where('product_id', $data['product_id']);
        } elseif (isset($data['product_set_item_id'])) {
            $query->where('product_set_item_id', $data['product_set_item_id']);
        } else {
            return null; // Neither product_id nor product_set_item_id provided
        }

        if ($query->exists()) {
            return null; // Favorite already exists
        }

        return Favorite::create($data);
    }

    public function delete($id)
    {
        $favorite = $this->find($id);

        if ($favorite) {
            $favorite->delete();
        }

        return $favorite;
    }
}
