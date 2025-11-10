<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'GET' => [],
            'POST' => ['question_id' => 'required|integer|exists:quiz_questions,id', 'answer_key' => 'required|string|in:A,B,C'],
            default => [],
        };
    }
}