<?php

namespace App\Http\Services\Product;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

class ProductImageService
{
    /**
     * Handle product images (store and associate)
     */
    public function handleProductImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $isMain = false;
            $imageFile = null;

            if (is_array($image)) {
                $isMain = isset($image['is_main']) && ($image['is_main'] == '1' || $image['is_main'] === 'true' || $image['is_main'] === true);
                $imageFile = $image['path'] ?? null;
            } else {
                $imageFile = $image;
            }

            if ($isMain) {
                $product->images()->update(['is_main' => false]);
            }

            if ($imageFile instanceof UploadedFile) {
                $path = $imageFile->store('products', 'public');
            } else {
                $path = $imageFile;
            }

            if ($path && is_string($path)) {
                $product->images()->create([
                    'image'   => $path,
                    'is_main' => $isMain,
                ]);
            }
        }
    }

    /**
     * Delete specific product images
     */
    public function deleteProductImages(Product $product, array $imageIds): void
    {
        $product->images()->whereIn('id', $imageIds)->delete();
    }
}
