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
            $rules += [
                'name'        => ['required', 'string', 'max:255'],
                'phone'       => ['required', 'string', 'max:255'],
                'email'       => ['nullable', 'email', 'max:255'],
                'session_id'  => ['required', 'string'],
                'address'     => ['required', 'string', 'max:255'],
                'city_id'     => ['nullable', 'exists:cities,id'],
                'district_id' => ['nullable', 'exists:districts,id'],
            ];
        } else {
            $rules['address_id'] = ['nullable', 'exists:addresses,id'];

            if (!$this->input('address_id')) {
                $rules += [
                    'address'     => ['required', 'string', 'max:255'],
                    'phone'       => ['required', 'string', 'max:255'],
                    'city_id'     => ['nullable', 'exists:cities,id'],
                    'district_id' => ['nullable', 'exists:districts,id'],
                ];
            }
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
