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
        $countryCode = $this->getCountryCodeFromIp();

        $country = Country::where('country_code', strtoupper($countryCode))->first();
        if ($country) {
            session(['country_id' => $country->id]);
        }

        $currency = $country
            ? Currency::where('country_code', strtoupper($countryCode))->first()
            : null;

        if ($currency) {
            $this->storeCurrency($currency->id, $sessionId, $isGuest);
            return $currency;
        }

        $default = $this->getDefaultCurrency();
        $this->storeCurrency($default?->id, $sessionId, $isGuest);
        return $default;
    }


    public function getDefaultCurrency(): ?Currency
    {
        return Currency::where('is_default', true)->first();
    }

    public function getCountryCodeFromIp(): ?string
    {
        try {
            $ip = request()->header('X-Forwarded-For') ?? request()->ip();

            if ($ip === '127.0.0.1' || $ip === '::1') {
                return 'EG';
            }

            $reader = new \GeoIp2\Database\Reader(storage_path('app/geoip/GeoLite2-Country.mmdb'));
            $record = $reader->country($ip);
            return $record->country->isoCode;
        } catch (\Exception $e) {
            \Log::error('GeoIP lookup failed', ['ip' => $ip ?? 'undefined', 'error' => $e->getMessage()]);
            return null;
        }
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