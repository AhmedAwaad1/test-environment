<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public function __construct($message = "Payment failed. Please try again.")
    {
        parent::__construct($message, 400);
    }
}
