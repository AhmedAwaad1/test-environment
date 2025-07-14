<?php

namespace App\Http\Controllers\Subscribe;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscribe\SendEmailToSubscribersRequest;
use App\Http\Requests\Subscribe\SubscribeRequest;
use App\Http\Services\Subscribe\SubscribeService;
use App\Mail\SendEmailToSubscribers;

class SubscribeController extends Controller
{
    public $SubscribeService;
    public function __construct(SubscribeService $SubscribeService)
    {
        $this->SubscribeService = $SubscribeService;
    }

    public function index(SubscribeRequest $request)
    {
        return $this->SubscribeService->getAllSubscribers($request);
    }

    public function store(SubscribeRequest $request)
    {
        return $this->SubscribeService->createSubscrption($request->validated());
    }
    public function sendEmailToSubscribers(SendEmailToSubscribersRequest $request)
    {
        return $this->SubscribeService->sendEmailToSubscribers($request->validated());
    }
}
