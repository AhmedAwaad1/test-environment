<?php

namespace App\Http\Services\GeoCurrency;

use App\Models\Country;
use App\Models\Currency;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;



class GeoCurrencyService
{
    public function getCurrencyForRequest(): ?Currency
    {
        // Hardcode Egypt logic
        $countryCode = 'EG';
        $country = Country::where('country_code', $countryCode)->first();
        
        if ($country) {
            session(['country_id' => $country->id]);
        }

        $currency = Currency::where('country_code', $countryCode)->first();

        if ($currency) {
            $this->storeCurrency($currency->id, null, false);
            return $currency;
        }

        return $this->getDefaultCurrency();
    }

    public function getDefaultCurrency(): ?Currency
    {
        return Currency::where('country_code', 'EG')->first() 
            ?? Currency::where('is_default', true)->first();
    }

    public function getCountryCodeFromIp(): string
    {
        return 'EG';
    }





    protected function storeCurrency(?int $currencyId, ?string $sessionId, bool $isGuest): void
    {
        if (!$currencyId) return;

        if ($isGuest && $sessionId) {
            Cache::put("currency_id_{$sessionId}", $currencyId, now()->addHours(12));
        } else {
            session(['currency_id' => $currencyId]);
        }
    }

    protected function isUserAuthenticated(): bool
    {
        return auth()->check();
    }

    public function resetForRequest(?string $sessionId = null): void
    {
        session()->forget(['currency_id', 'country_id']);
        if ($sessionId) {
            Cache::forget("currency_id_{$sessionId}");
            Cache::forget("country_id_{$sessionId}");
        }
    }
}