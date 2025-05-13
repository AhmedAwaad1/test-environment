<?php

namespace App\Repositories\Favorite;

use App\Models\Favorite;
use Illuminate\Support\Facades\Storage;

class FavoriteRepository
{
    public function getAll()
    {
        return Favorite::where('user_id', auth()->id())
            ->with('product.images');
    }

    public function find($id)
    {
        return Favorite::where('user_id', auth()->id())
        ->where('id', $id)
        ->with('product', 'user')
        ->find($id);
    }

    public function create(array $data)
    {
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
