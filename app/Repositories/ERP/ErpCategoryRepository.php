<?php

namespace App\Repositories\ERP;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Support\Str;

class ErpCategoryRepository
{
    public function all(): array
    {
        return [
            'level1' => Category::orderBy('code')->get(),
            'level2' => SubCategory::orderBy('code')->get(),
            'level3' => SubSubCategory::orderBy('code')->get(),
        ];
    }

    public function findCategoryByCode($code)
    {
        return Category::where('code', $code)->first();
    }

    public function findSubCategoryByCode($code)
    {
        return SubCategory::where('code', $code)->first();
    }

    public function findSubSubCategoryByCode($code)
    {
        return SubSubCategory::where('code', $code)->first();
    }

    public function upsertLevel1(array $data): Category
    {
        $category = $this->findCategoryByCode($data['code']);

        $name = $data['name'];
        $notes = $data['notes'] ?? null;

        if ($category) {
            $category->update([
                'notes' => $notes,
            ]);
            return $category->fresh();
        }

        $slug = $this->generateUniqueSlug($name, $data['code'], Category::class);

        return Category::create([
            'code' => $data['code'],
            'name_ar' => $name,
            'name_en' => $name,
            'slug' => $slug,
            'notes' => $notes,
            'is_active' => 1,
            'order' => 0,
        ]);
    }

    public function upsertLevel2(array $data, Category $parentCategory): SubCategory
    {
        $subCategory = $this->findSubCategoryByCode($data['code']);

        $name = $data['name'];
        $notes = $data['notes'] ?? null;

        if ($subCategory) {
            $subCategory->update([
                'category_id' => $parentCategory->id,
                'ucode1' => $data['ucode1'],
                'notes' => $notes,
            ]);
            return $subCategory->fresh();
        }

        $slug = $this->generateUniqueSlug($name, $data['code'], SubCategory::class);

        return SubCategory::create([
            'code' => $data['code'],
            'ucode1' => $data['ucode1'],
            'category_id' => $parentCategory->id,
            'name_ar' => $name,
            'name_en' => $name,
            'slug' => $slug,
            'notes' => $notes,
            'is_active' => 1,
            'order' => 0,
        ]);
    }

    public function upsertLevel3(array $data, ?Category $mainCategory, SubCategory $subCategory): SubSubCategory
    {
        $subSubCategory = $this->findSubSubCategoryByCode($data['code']);

        $name = $data['name'];
        $notes = $data['notes'] ?? null;

        if ($subSubCategory) {
            $subSubCategory->update([
                'category_id' => $mainCategory?->id ?? $subCategory->category_id,
                'sub_category_id' => $subCategory->id,
                'ucode1' => $data['ucode1'],
                'ucode2' => $data['ucode2'],
                'name_ar' => $name,
                'name_en' => $subSubCategory->name_en ?: $name,
                'notes' => $notes,
            ]);
            return $subSubCategory->fresh();
        }

        $slug = $this->generateUniqueSlug($name, $data['code'], SubSubCategory::class);

        return SubSubCategory::create([
            'code' => $data['code'],
            'ucode1' => $data['ucode1'],
            'ucode2' => $data['ucode2'],
            'category_id' => $mainCategory?->id ?? $subCategory->category_id,
            'sub_category_id' => $subCategory->id,
            'name_ar' => $name,
            'name_en' => $name,
            'slug' => $slug,
            'notes' => $notes,
            'is_active' => 1,
            'order' => 0,
        ]);
    }

    protected function generateUniqueSlug(string $name, $code, string $modelClass): string
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'cat-' . $code;
        }

        $slug = $baseSlug;
        $counter = 1;
        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
