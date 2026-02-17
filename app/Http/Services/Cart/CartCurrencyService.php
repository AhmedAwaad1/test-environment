<?php

namespace App\Http\Services\Cart;

use App\Models\Cart;
use App\Models\Currency;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Support\Facades\Cache;

class CartCurrencyService
{
    /**
     * Handle cart currency, forcing EGP if necessary.
     */
    public function handleCartCurrency(Cart $cart, $sessionId = null): void
    {
        // --- Force EGP (Egypt) ---
        $egpCurrency = Currency::where('name', 'EGP')->first();
        
        // Fallback if EGP isn't in DB
        if (!$egpCurrency) {
             $geo = app(GeoCurrencyService::class);
             $egpCurrency = $geo->getDefaultCurrency();
        }

        // Always enforce EGP currency
        if ($egpCurrency && (!$cart->currency_id || $cart->currency_id !== $egpCurrency->id)) {
            $cart->currency_id = $egpCurrency->id;
            $cart->save();
        }

        // Update session/cache to reflect this forced currency
        session(['currency_id' => $cart->currency_id]);
        if (!empty($sessionId)) {
            Cache::put("currency_id_{$sessionId}", $cart->currency_id, now()->addDays(30));
        }
    }
}
