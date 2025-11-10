<?php

namespace App\Http\Services\Quiz;

use App\Repositories\Quiz\QuizRepository;
use App\Http\Resources\Quiz\QuizQuestionResource;
use App\Http\Resources\Quiz\QuizResultResource;
use Illuminate\Support\Facades\Response;

class QuizService
{
    protected $repo;

    public function __construct(QuizRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getQuestion($id)
    {
        $question = $this->repo->getQuestionById($id);
        if (!$question) return Response::errorResponse('Question not found', [], 404);
        return Response::successResponse(new QuizQuestionResource($question), 'Question retrieved successfully');
    }

    public function submitAnswer($request)
    {
        $result = $this->repo->getAnswerResult($request['answer_key']);
        if (!$result) return Response::errorResponse('Result not found', [], 404);
        return Response::successResponse(new QuizResultResource($result), 'Result retrieved successfully');
    }
}