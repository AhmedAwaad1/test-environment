<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSubscribers extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subjectLine, $message;

    public function __construct($subjectLine, $message)
    {
        $this->message = $message;
        $this->subjectLine = $subjectLine;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->markdown('emails.subscribers');
    }
}

