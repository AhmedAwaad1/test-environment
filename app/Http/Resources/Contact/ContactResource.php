<?php

namespace App\Http\Resources\Contact;

use App\Http\Resources\CitizenshipDetail\CitizenshipBenefitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'checked' => $this->checked ?? false,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
