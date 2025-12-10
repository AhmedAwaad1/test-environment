<?php

namespace App\Http\Services\Payment;

use App\Http\Interfaces\Payment\PaymentHandlerInterface;
use App\Http\Services\Payment\CodPaymentHandler;
use App\Http\Services\Payment\StripePaymentHandler;

class PaymentFactoryService
{
    public function make(string $method): ? PaymentHandlerInterface
    {
        switch ($method) {
            case 'card':
                return new CardPaymentHandler();
            case 'cod':
                return new CodPaymentHandler();
            default:
                return null;
        }
    }
}
