<?php

namespace App\Http\Services\GeoCurrency;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class GeoCurrencyService
{
    public function getCurrencyForRequest(): ?Currency
    {

        $sessionId = request()->input('session_id');
        $isGuest = !$this->isUserAuthenticated();

        if ($isGuest && $sessionId) {
            $cacheKey = "currency_id_{$sessionId}";

            if (Cache::has($cacheKey)) {
                return Currency::find(Cache::get($cacheKey));
            }
        } else {
            if (session()->has('currency_id')) {
                return Currency::find(session('currency_id'));
            }
        }

        $ip = request()->ip();
        $countryCode = $this->getCountryCodeFromIp($ip);
        Log::channel('product_price')->info('GeoIP currency lookup triggered', [
            'country_code' => $countryCode,
            'ip' => $ip
        ]);

        $currency = $countryCode
            ? Currency::where('country_code', strtoupper($countryCode))->first()
            : null;

        if ($currency) {
            $this->storeCurrency($currency->id, $sessionId, $isGuest);
            return $currency;
        }

        $default = $this->getDefaultCurrency();
        Log::channel('product_price')->warning('Fallback to default currency', [
            'ip' => $ip,
            'country_code' => $countryCode,
            'default_currency_id' => $default?->id,
        ]);

        $this->storeCurrency($default?->id, $sessionId, $isGuest);
        return $default;
    }

    public function getDefaultCurrency(): ?Currency
    {
        return Currency::where('is_default', true)->first();
    }

    protected function getCountryCodeFromIp($ip): ?string
    {
        return 'EG'; // فرض إنك من مصر
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
}