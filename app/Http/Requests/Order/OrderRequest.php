<?php

namespace App\Http\Requests\Order;

use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return match ($this->method()) {
            'GET' => $this->indexRules(),
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            'DELETE' => [],
            default => [],
        };
    }


    private function indexRules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:pending,processing,completed,canceled'],
        ];
    }

    private function storeRules(): array
    {
        $rules = [
            'payment_method' => ['required', 'string', 'in:cod,paymob,stripe'],
        ];

        if (!auth()->check()) {

            $geoService = new GeoCurrencyService();
            $geoService->getCurrencyForRequest();

            $countryId = session('country_id');
            $country = \App\Models\Country::find($countryId);
            $hasCountryShipping = $country && $country->shipping_price > 0;

            $rules = array_merge($rules, [
                'name'         => ['required', 'string', 'max:255'],
                'phone'        => ['required', 'string', 'max:255'],
                'email'        => ['nullable', 'email', 'max:255'],
                'session_id'   => ['required', 'string'],

                'address'      => ['required', 'string', 'max:255'],
                'city_id'      => $hasCountryShipping ? ['nullable', 'exists:cities,id'] : ['required', 'exists:cities,id'],
                'district_id'  => $hasCountryShipping ? ['nullable', 'exists:districts,id'] : ['required', 'exists:districts,id'],
            ]);
        } else {
            $rules['address_id'] = ['required', 'exists:addresses,id'];
        }

        return $rules;
    }




    private function updateRules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,processing,shipped,cancelled,delivered'],
        ];
    }
}
