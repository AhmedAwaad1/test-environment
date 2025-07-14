<?php

namespace App\Http\Services\Subscribe;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Subscribe\SubscribeResource;
use App\Mail\SendEmailToSubscribers;
use App\Repositories\Subscribe\SubscribeRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class SubscribeService
{
    protected $subscribeRepo;

    public function __construct(SubscribeRepository $subscribeRepo)
    {
        $this->subscribeRepo = $subscribeRepo;
    }


    public function getAllSubscribers($request)
    {
        $query = $this->subscribeRepo->getAll($request->all());

        if ($request->per_page) {
            $subscribers = new PaginationResource($query->paginate($request->per_page), SubscribeResource::class);
        } else {
            $subscribers = SubscribeResource::collection($query->get());
        }

        return Response::successResponse($subscribers, 'subscribers retrieved successfully');
    }

    public function createSubscrption($request)
    {
        try {
            $subscribe = $this->subscribeRepo->create($request);

            return Response::successResponse(new SubscribeResource($subscribe), 'subscribe created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create subscribe');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create subscribe');
        }
    }

    public function sendEmailToSubscribers($request)
    {
        try {
            $subscribers = $this->subscribeRepo->getAllSubscribers();

            foreach ($subscribers as $subscriber) {
                // Send an email to each subscriber
                Mail::to($subscriber->email)
                    ->queue(new SendEmailToSubscribers($request->subject, $request->message));
            }

            return Response::successResponse([], 'Emails sent to all subscribers successfully');

        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to send emails to subscribers');
        }
    }
}
