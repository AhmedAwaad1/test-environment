<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct($message = "Insufficient stock for one or more items in your cart.")
    {
        parent::__construct($message, 422);
    }
}
