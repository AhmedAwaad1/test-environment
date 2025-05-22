<?php

namespace App\Http\Services\ProductVariant;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Models\ProductVariantImage;
use App\Repositories\ProductVariant\ProductVariantRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProductVariantService
{
    protected $productVariantRepo;

    public function __construct(ProductVariantRepository $productVariantRepo)
    {
        $this->productVariantRepo = $productVariantRepo;
    }


    public function getAllProductVariants($request)
    {
        $query = $this->productVariantRepo->getAll($request);

        if ($request->per_page) {
            $productVariants = new PaginationResource($query->paginate($request->per_page), ProductVariantResource::class);
        } else {
            $productVariants = ProductVariantResource::collection($query->get());
        }

        return Response::successResponse($productVariants, 'product variants retrieved successfully');
    }

    public function getProductVariantById($id)
    {
        try {
            $productVariant = $this->productVariantRepo->find($id);

            if (!$productVariant) {
                return Response::errorResponse('product variant not found', [], 404);
            }

            return Response::successResponse(new ProductVariantResource($productVariant), 'product variant found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'product variant');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product variant');
        }
    }

    public function createProductVariant($request)
    {
        try {
            DB::beginTransaction();

            // create the productVariant
            $productVariants = $this->productVariantRepo->create($request);

            // Handle images
            if (isset($request['images'])) {
                $imagesData = array_map(function($image) {
                    $path = $image->store('variants', 'public');
                    return ['image' => $path];
                }, $request['images']);

                // attach the images to the first variant (because images for sizes are the same)
                $productVariants[0]->images()->createMany($imagesData);
            }

            DB::commit();

            return Response::successResponse(new ProductVariantResource($productVariants), 'product variant created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Response::handleDatabaseException($e, 'create product variant');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'create product variant');
        }
    }

    public function updateProductVariant($id, array $data)
    {
        try {
            $productVariant = $this->productVariantRepo->find($id);

            if (!$productVariant) {
                return Response::errorResponse('Product variant not found', [], 404);
            }

            DB::beginTransaction();

            // Update the productVariant details
            $this->productVariantRepo->update($id, $data);

            // Handle new uploaded images
            if (isset($data['images'])) {
                foreach ($data['images'] as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $path = $image->store('variants', 'public');
                        $productVariant->images()->create([
                            'image' => $path,
                        ]);
                    } elseif (is_array($image) && isset($image['path'])) {
                        $productVariant->images()->create([
                            'image' => $image['path'],
                        ]);
                    }
                }
            }

            // Handle deleted images
            if (isset($data['deleted_images'])) {
                foreach ($data['deleted_images'] as $image_id) {
                    $image = $productVariant->images()->find($image_id);
                    if ($image) {
                        if (!empty($image->image) && Storage::disk('public')->exists($image->image)) {
                            Storage::disk('public')->delete($image->image);
                        }
                        $image->delete();
                    }
                }
            }

            DB::commit();

            return Response::successResponse(
                new ProductVariantResource($productVariant->load('images')),
                'ProductVariant updated successfully'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return Response::handleModelNotFoundException($e, 'product variant');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'update product variant');
        }
    }

    public function deleteProductVariant($id)
    {
        $productVariant = $this->productVariantRepo->find($id);

        if (!$productVariant) {
            return Response::errorResponse('product variant not found', [], 404);
        }

        $this->productVariantRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'product variant deleted successfully');
    }

}
