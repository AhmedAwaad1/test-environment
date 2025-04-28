<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\ProductImage;
use App\Repositories\Product\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    protected $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }


    public function getAllProducts($request)
    {
        $query = $this->productRepo->getAll($request);

        if ($request->per_page) {
            $products = new PaginationResource($query->paginate($request->per_page), ProductResource::class);
        } else {
            $products = ProductResource::collection($query->get());
        }

        return Response::successResponse($products, 'products retrieved successfully');
    }

    public function getProductById($id)
    {
        try {
            $product = $this->productRepo->find($id);

            if (!$product) {
                return Response::errorResponse('product not found', [], 404);
            }

            return Response::successResponse(new ProductResource($product), 'product found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'product');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product');
        }
    }

    public function createProduct($request)
    {
        try {
            DB::beginTransaction();

            // create the product
            $product = $this->productRepo->create($request);

            // Handle images
            if (isset($request['images'])) {

                // Check if more than one image is marked as main
                $mainImages = array_filter($request['images'], function($img) {
                    return isset($img['is_main']) && $img['is_main'];
                });

                if (count($mainImages) > 1) {
                    return Response::errorResponse('Only one image can be set as main.', [], 422);
                }

                foreach ($request['images'] as $image) {
                    $path = $image['path'];
                    $isMain = isset($image['is_main']) ? filter_var($image['is_main'], FILTER_VALIDATE_BOOLEAN) : false;

                    $product->images()->create([
                        'image' => $path,
                        'is_main' => $isMain,
                    ]);
                }
            }

            DB::commit();

            return Response::successResponse(new ProductResource($product), 'product created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Response::handleDatabaseException($e, 'create product');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'create product');
        }
    }

    public function updateProduct($id, array $data)
    {
        try {
            // Find the product by ID
            $product = $this->productRepo->find($id);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            DB::beginTransaction();

            // Update the product details
            $this->productRepo->update($id, $data);

            // Handle new images
            if (isset($data['images'])) {

                // Check if more than one image is marked as main
                $mainImageId = $data['main_image_id'] ?? null;

                // Update the main image
                if ($mainImageId) {
                    // Set all images to not be main
                    $product->images()->update(['is_main' => false]);

                    // Set the selected image as main
                    $product->images()->where('id', $mainImageId)->update(['is_main' => true]);
                }

                // Add or update the images
                foreach ($data['images'] as $image) {
                    // Skip the image if it's the main image (it's already updated)
                    if (isset($image['id']) && $image['id'] == $mainImageId) {
                        continue;
                    }

                    $path = $image['path'];
                    $isMain = isset($image['is_main']) ? filter_var($image['is_main'], FILTER_VALIDATE_BOOLEAN) : false;

                    // Add the new image or update existing one
                    $product->images()->updateOrCreate(
                        ['image' => $path],
                        ['is_main' => $isMain]
                    );
                }
            }

            // Handle deleted images
            if (isset($data['deleted_images'])) {
                foreach ($data['deleted_images'] as $image_id) {
                    $image = $product->images()->find($image_id);
                    if ($image && $image->image) {
                        // Delete the image from storage
                        Storage::delete($image->file_path);
                        // Delete the record from the database
                        $image->delete();
                    }
                }
            }

            DB::commit();

            return Response::successResponse(new ProductResource($product), 'Product updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return Response::handleModelNotFoundException($e, 'product');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'update product');
        }
    }


    public function deleteProduct($id)
    {
        $product = $this->productRepo->find($id);

        if (!$product) {
            return Response::errorResponse('product not found', [], 404);
        }

        $this->productRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'product deleted successfully');
    }

}
