<?php

namespace App\Services\Product;

use App\Repositories\Product\ProductRepository;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        protected ProductRepository $repository
    ) {}

    public function getAll($request)
    {
        $perPage = $request->get('per_page', 10);
        return $this->repository->getAll($perPage);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function create($request)
    {
        $data = $request->validated();

        // Handle image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->handleImageUploads($data['images']);
        }

        return $this->repository->create($data);
    }

    public function update($id, $request)
    {
        $data = $request->validated();

        // Handle image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->handleImageUploads($data['images']);
        }

        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    protected function handleImageUploads($images)
    {
        $processedImages = [];
        foreach ($images as $image) {
            if (isset($image['path']) && $image['path']->isValid()) {
                $path = $image['path']->store('products', 'public');
                $processedImages[] = [
                    'path' => $path,
                    'is_main' => $image['is_main'] ?? false,
                ];
            }
        }
        return $processedImages;
    }
}
