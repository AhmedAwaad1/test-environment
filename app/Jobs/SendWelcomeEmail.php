<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userEmail;

    public function __construct($userEmail)
    {
        $this->userEmail = $userEmail;
    }

    public function handle(): void
    {
        Mail::send('emails.welcome-email', [], function ($message) {
            $message->to($this->userEmail)
                    ->subject('Welcome to Najd!');
        });
    }
}
