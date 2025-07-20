<?php

namespace App\Http\Middleware;

use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrencyFromIp
{
    protected GeoCurrencyService $geoCurrencyService;

    public function __construct(GeoCurrencyService $geoCurrencyService)
    {
        $this->geoCurrencyService = $geoCurrencyService;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('currency_id')) {
            $currency = $this->geoCurrencyService->getCurrencyForRequest();
            if ($currency) {
                session(['currency_id' => $currency->id]);
            }
        }


        return $next($request);
    }
}
