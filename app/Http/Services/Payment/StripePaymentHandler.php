<?php

namespace App\Http\Services\Payment;

use App\Http\Interfaces\Payment\PaymentHandlerInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class StripePaymentHandler implements PaymentHandlerInterface
{
    public function pay(Order $order)
    {

        // Log::info("Processing Stripe payment for order #{$order->id}");

        // return [
        //     'status' => 'success',
        //     'message' => 'Paid with Stripe'
        // ];
    }
}

