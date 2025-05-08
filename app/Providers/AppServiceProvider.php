<?php

namespace App\Providers;

use App\Http\Mixins\ResponseMixins;
use App\Http\Services\Payment\PaymentFactoryService;
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
    }

}
