<?php

namespace App\Http\Services\ERP;

use App\Http\Resources\ERP\ErpCategoryResource;
use App\Repositories\ERP\ErpCategoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class ErpCategoryService
{
    public function __construct(
        protected ErpCategoryRepository $categoryRepo
    ) {}

    public function syncCategories(array $categories)
    {
        $synced = DB::transaction(function () use ($categories) {
            // A batch can contain its parents and children in any order.
            usort($categories, fn (array $left, array $right) => $this->level($left) <=> $this->level($right));

            return array_map(fn (array $data) => $this->syncCategory($data), $categories);
        });

        $resources = array_map(
            fn (array $category) => new ErpCategoryResource($category['model'], $category['level']),
            $synced
        );

        return Response::successResponse(
            count($resources) === 1 ? $resources[0] : $resources,
            'Categories synchronized successfully',
            201
        );
    }

    public function listCategories()
    {
        $categories = $this->categoryRepo->all();

        return Response::successResponse([
            'level1' => $categories['level1']->map(fn ($category) => (new ErpCategoryResource($category, 1))->resolve()),
            'level2' => $categories['level2']->map(fn ($category) => (new ErpCategoryResource($category, 2))->resolve()),
            'level3' => $categories['level3']->map(fn ($category) => (new ErpCategoryResource($category, 3))->resolve()),
        ], 'ERP categories retrieved successfully');
    }

    private function syncCategory(array $data): array
    {
        $ucode1 = (int) $data['ucode1'];
        $ucode2 = (int) $data['ucode2'];

        if ($ucode1 === 0 && $ucode2 === 0) {
            return ['model' => $this->categoryRepo->upsertLevel1($data), 'level' => 1];
        }

        if ($ucode1 !== 0 && $ucode2 === 0) {
            $parentCategory = $this->categoryRepo->findCategoryByCode($ucode1);
            if (!$parentCategory) {
                throw ValidationException::withMessages(['ucode1' => ["Level 1 parent with code {$ucode1} does not exist."]]);
            }

            return ['model' => $this->categoryRepo->upsertLevel2($data, $parentCategory), 'level' => 2];
        }

        if ($ucode1 !== 0 && $ucode2 !== 0) {
            $mainCategory = $this->categoryRepo->findCategoryByCode($ucode1);
            $subCategory = $this->categoryRepo->findSubCategoryByCode($ucode2);

            if (!$mainCategory) {
                throw ValidationException::withMessages(['ucode1' => ["Level 1 parent with code {$ucode1} does not exist."]]);
            }
            if (!$subCategory) {
                throw ValidationException::withMessages(['ucode2' => ["Level 2 parent with code {$ucode2} does not exist."]]);
            }
            if ($subCategory->category_id !== $mainCategory->id) {
                throw ValidationException::withMessages(['ucode2' => ["Level 2 parent {$ucode2} does not belong to Level 1 parent {$ucode1}."]]);
            }

            return ['model' => $this->categoryRepo->upsertLevel3($data, $mainCategory, $subCategory), 'level' => 3];
        }

        throw ValidationException::withMessages([
            'ucode1' => ['Invalid hierarchy. Only Levels 1, 2, and 3 are allowed.'],
        ]);
    }

    private function level(array $data): int
    {
        if ((int) $data['ucode1'] === 0 && (int) $data['ucode2'] === 0) {
            return 1;
        }

        return (int) $data['ucode2'] === 0 ? 2 : 3;
    }
}
