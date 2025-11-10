<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\QuizRequest;
use App\Http\Services\Quiz\QuizService;

class QuizController extends Controller
{
    protected $service;

    public function __construct(QuizService $service)
    {
        $this->service = $service;
    }

    public function show($id){
        return $this->service->getQuestion($id);
    }

    public function submit(QuizRequest $request)
    {
        return $this->service->submitAnswer($request->validated());
    }
}