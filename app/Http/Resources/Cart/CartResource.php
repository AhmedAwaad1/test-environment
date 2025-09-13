<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\CartItem\CartItemResource;
use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Country;
use App\Http\Services\GeoCurrency\GeoCurrencyService;


class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $totalPrice     = (float)($this->total_price ?? 0);
        $discountAmount = (float)($this->discount_amount ?? 0);
        $totalAfterDisc = max(0, $totalPrice - $discountAmount);

        $allowedGulf = ['SA', 'AE', 'KW', 'QA', 'OM', 'BH'];

        $country = null;
        if (session()->has('country_id')) {
            $country = Country::find(session('country_id'));
        }

        if (!$country) {
            $code = app(GeoCurrencyService::class)->getCountryCodeFromIp();
            if ($code) {
                $country = Country::where('country_code', strtoupper($code))->first();
            }
        }

        if (!$country || !in_array($country->country_code, $allowedGulf, true)) {
            $country = Country::where('country_code', 'KW')->first();
        }

        $shippingPrice = (float)($country->shipping_price ?? 0.0);
        $grandTotal    = round($totalAfterDisc + $shippingPrice, 2);

        return [
            'id'                         => $this->id,
            'user_id'                    => $this->user_id,
            'session_id'                 => $this->session_id,
            'discount_amount'            => round($discountAmount, 2),
            'total_price'                => round($totalPrice, 2),
            'total_price_after_discount' => $totalAfterDisc,

            'shipping' => [
                'country_id'      => $country?->id,
                'country_code'    => $country?->country_code,
                'country_name_en' => $country?->name_en,
                'country_name_ar' => $country?->name_ar,
                'price'           => $shippingPrice,
            ],

            'grand_total' => $grandTotal,

            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'user'       => new AuthResource($this->whenLoaded('user')),

//            'debug_geo' => [
//                'header_ip'   => $request->header('X-Forwarded-For'),
//                'resolved_ip' => $request->ip(),
//                'country'     => app(GeoCurrencyService::class)->getCountryCodeFromIp(),
//            ],
        ];
    }

}
