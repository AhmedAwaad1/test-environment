<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userEmail;
    public $verificationCode;

    public function __construct($userEmail, $verificationCode)
    {
        $this->userEmail = $userEmail;
        $this->verificationCode = $verificationCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::send('emails.verification_code', [
                'verificationCode' => $this->verificationCode,
            ], function ($message) {
                $message->to($this->userEmail)
                        ->subject('Email Verification Code');
            });

            Log::channel('email')->info("Email sent successfully to: {$this->userEmail}");
        } catch (\Exception $e) {
            Log::channel('email')->error("Failed to send email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
