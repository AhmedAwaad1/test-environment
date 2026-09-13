<?php

namespace App\Http\Services\ERP;

use App\Http\Resources\ERP\ErpProductResource;
use App\Repositories\ERP\ErpCategoryRepository;
use App\Repositories\ERP\ErpProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class ErpProductService
{
    public function __construct(
        protected ErpProductRepository $productRepo,
        protected ErpCategoryRepository $categoryRepo
    ) {}

    public function storeProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            if ($this->productRepo->findByExternalId((string) $data['id'])) {
                throw ValidationException::withMessages(['id' => ["A product with ERP ID '{$data['id']}' already exists."]]);
            }

            $groups = $this->resolveProductHierarchy($data);
            $productData = [
                'external_id' => (string) $data['id'],
                'name_ar' => $data['name'],
                'name_en' => $data['name2'] ?? $data['name'],
                'description_ar' => $data['description'] ?? null,
                'description_en' => $data['description2'] ?? null,
                'price' => (float) $data['price'],
                'quantity' => (int) ($data['stock'] ?? 0),
                'image_url' => $data['image_url'] ?? null,
                ...$groups,
            ];

            $product = $this->productRepo->upsertProduct($productData);

            return Response::successResponse(
                new ErpProductResource($product),
                'Product synchronized successfully',
                201
            );
        });
    }

    public function bulkUpsertProducts(array $products)
    {
        return DB::transaction(function () use ($products) {
            $created = 0;
            $updated = 0;

            foreach ($products as $data) {
                $product = $this->productRepo->findByExternalId((string) $data['id']);

                if ($product) {
                    if (!array_key_exists('stock', $data) && array_key_exists('quantity', $data)) {
                        $data['stock'] = $data['quantity'];
                    }

                    $this->productRepo->updatePriceAndStock($product, $data);
                    $updated++;
                    continue;
                }

                $groups = $this->resolveProductHierarchy($data);
                $nameAr = $data['name_ar'] ?? $data['name'];
                $this->productRepo->createProduct([
                    'external_id' => (string) $data['id'],
                    'name_ar' => $nameAr,
                    'name_en' => $data['name_en'] ?? $data['name2'] ?? $nameAr,
                    'description_ar' => $data['description_ar'] ?? $data['description'] ?? null,
                    'description_en' => $data['description_en'] ?? $data['description2'] ?? null,
                    'price' => (float) $data['price'],
                    'quantity' => (int) ($data['stock'] ?? $data['quantity'] ?? 0),
                    'image_url' => $data['image_url'] ?? null,
                    ...$groups,
                ]);
                $created++;
            }

            return Response::successResponse([
                'created' => $created,
                'updated' => $updated,
            ], 'ERP products synchronized successfully', 200);
        });
    }

    public function updateProduct(string $externalId, array $data)
    {
        return DB::transaction(function () use ($externalId, $data) {
            $product = $this->productRepo->findByExternalId($externalId);

            if (!$product) {
                return Response::errorResponse(
                    "Product with ERP ID '{$externalId}' not found",
                    [],
                    404
                );
            }

            if ($this->hasGroupModification($data)) {
                $data = [...$data, ...$this->resolveProductHierarchy([
                    'group_code' => array_key_exists('group_code', $data) ? $data['group_code'] : $product->erp_group_code,
                    'group2_code' => array_key_exists('group2_code', $data) ? $data['group2_code'] : $product->erp_group2_code,
                    'group3_code' => array_key_exists('group3_code', $data) ? $data['group3_code'] : $product->erp_group3_code,
                ])];
            }

            $updatedProduct = $this->productRepo->updateProductByExternalId($product, $data);

            return Response::successResponse(
                new ErpProductResource($updatedProduct),
                'Product updated successfully'
            );
        });
    }

    public function listProducts(int $perPage)
    {
        return ErpProductResource::collection($this->productRepo->paginate($perPage));
    }

    public function getProduct(string $externalId)
    {
        $product = $this->productRepo->findByExternalId($externalId);
        if (!$product) {
            return Response::errorResponse("Product with ERP ID '{$externalId}' not found", [], 404);
        }

        return Response::successResponse(new ErpProductResource($product), 'ERP product retrieved successfully');
    }

    public function deleteProduct(string $externalId)
    {
        $product = $this->productRepo->findByExternalId($externalId);
        if (!$product) {
            return Response::errorResponse("Product with ERP ID '{$externalId}' not found", [], 404);
        }
        if ($this->productRepo->hasCommercialReferences($product)) {
            return Response::errorResponse('Product cannot be deleted because it is referenced by an existing order or transaction.', [], 409);
        }

        $this->productRepo->delete($product);

        return Response::successResponse(['external_id' => $externalId], 'ERP product deleted successfully');
    }

    private function hasGroupModification(array $data): bool
    {
        return array_key_exists('group_code', $data)
            || array_key_exists('group2_code', $data)
            || array_key_exists('group3_code', $data);
    }

    private function resolveProductHierarchy(array $groups): array
    {
        $groupCode = $groups['group_code'] ?? null;
        $group2Code = $groups['group2_code'] ?? null;
        $group3Code = $groups['group3_code'] ?? null;

        $category = $this->categoryRepo->findCategoryByCode($groupCode);
        if (!$category) {
            throw ValidationException::withMessages(['group_code' => ["Level 1 group code {$groupCode} does not exist."]]);
        }

        $subCategory = null;
        if ($group2Code !== null) {
            $subCategory = $this->categoryRepo->findSubCategoryByCode($group2Code);
            if (!$subCategory || $subCategory->category_id !== $category->id) {
                throw ValidationException::withMessages(['group2_code' => ["Level 2 group code {$group2Code} does not belong to Level 1 group code {$groupCode}."]]);
            }
        }

        $subSubCategory = null;
        if ($group3Code !== null) {
            $subSubCategory = $this->categoryRepo->findSubSubCategoryByCode($group3Code);
            if (!$subCategory || !$subSubCategory || $subSubCategory->category_id !== $category->id || $subSubCategory->sub_category_id !== $subCategory->id) {
                throw ValidationException::withMessages(['group3_code' => ["Level 3 group code {$group3Code} does not belong to Level 2 group code {$group2Code} and Level 1 group code {$groupCode}."]]);
            }
        }

        return [
            'category_id' => $category->id,
            'sub_category_id' => $subCategory?->id,
            'sub_sub_category_id' => $subSubCategory?->id,
            'erp_group_code' => $groupCode,
            'erp_group2_code' => $group2Code,
            'erp_group3_code' => $group3Code,
        ];
    }
}
