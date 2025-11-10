<?php

namespace App\Repositories\Quiz;

use App\Models\QuizQuestion;
use App\Models\QuizResult;

class QuizRepository
{
    public function getQuestionById($id)
    {
        return QuizQuestion::with('answers')->find($id);
    }

    public function getAnswerResult($key)
    {
        return QuizResult::where('result_key', $key)->first();
    }
}