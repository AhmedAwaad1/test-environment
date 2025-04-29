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
                    $path = $image['image'];
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
            $product = $this->productRepo->find($id);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            DB::beginTransaction();

            // Update the product details
            $this->productRepo->update($id, $data);

            // Handle new images
            if (isset($data['images'])) {
                foreach ($data['images'] as $image) {
                    $path = $image['path'];

                    $product->images()->create([
                        'image' => $path,
                        'is_main' => false,
                    ]);
                }
            }

            // Handle deleted images
            if (isset($data['deleted_images'])) {
                foreach ($data['deleted_images'] as $image_id) {
                    $image = $product->images()->find($image_id);
                    if ($image) {
                        if (!empty($image->image) && Storage::exists($image->image)) {
                            Storage::delete($image->image);
                        }
                        $image->delete();
                    }
                }
            }

            // Handle setting main image
            if (isset($data['main_image_id'])) {
                $mainImageId = $data['main_image_id'];

                // First, set all images to is_main = false
                $product->images()->update(['is_main' => false]);

                // Then, set the selected image to is_main = true
                ProductImage::where('product_id', $product->id)
                ->update(['is_main' => false]);

                ProductImage::where('product_id', $product->id)
                ->where('id', $mainImageId)
                ->update(['is_main' => true]);
            }

            DB::commit();

            return Response::successResponse(new ProductResource($product->load('images')), 'Product updated successfully');

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
