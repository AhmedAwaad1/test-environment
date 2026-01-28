<?php

namespace App\Providers;

use App\Http\Mixins\ResponseMixins;
use App\Http\Services\Payment\PaymentFactoryService;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Observers\Order\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
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
        $this->app->singleton(PaymentFactoryService::class, function () {
            return new PaymentFactoryService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Response::mixin(new ResponseMixins());
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
    }

}
