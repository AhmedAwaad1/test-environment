<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
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
    public function rules()
    {
        $endPoint = $this->segment(count($this->segments()));

        switch ($endPoint) {
            case '':
                return $this->allValidation();
            case 'create':
                return $this->createValidation();
            case 'update':
                return $this->updateValidation();
            case 'delete':
                return $this->idValidation();
            case 'get':
                return $this->idValidation();
            default:
                if (is_numeric($endPoint)) {
                    return $this->idValidation();
                }
                return [];
        }
    }


    public function createValidation()
    {
        return [
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'content_en' => 'required|string',
            'content_ar' => 'required|string',

        ];
    }

    public function updateValidation()
    {
        return [
            'id' => ['required', 'exists:blogs,id'],
            'title_en' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'content_en' => 'nullable|string',
            'content_ar' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
    public function idValidation()
    {
        return [
            'id' => ['required', 'exists:blogs,id'],
        ];
    }

    public function allValidation()
    {
        return [
            'per_page' => ['nullable', 'integer'],
        ];
    }
}
