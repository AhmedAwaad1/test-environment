<?php

namespace App\Providers;

use App\Http\Mixins\ResponseMixins;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Response::mixin(new ResponseMixins());

        Validator::extend('one_field_only', function ($attribute, $value, $parameters, $validator) {
            $product_id = request()->input('product_id');
            $category_id = request()->input('category_id');
            $product_type_id = request()->input('product_type_id');

            // Determine which fields have data
            $fields = [$product_id, $category_id, $product_type_id];
            $filledFields = array_filter($fields);

            // If more than one field has data, return false (error)
            return count($filledFields) <= 1;
        });

    }
}
