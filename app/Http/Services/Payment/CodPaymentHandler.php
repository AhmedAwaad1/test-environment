<?php

namespace App\Http\Services\Payment;

use App\Http\Interfaces\Payment\PaymentHandlerInterface;
use App\Models\Order;
use App\Models\Payment;

class CodPaymentHandler implements PaymentHandlerInterface
{
    public function pay(Order $order):array
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'status' => 'pending',
            'amount' => $order->total_price,
        ]);

        return [
            'success' => true,
            'message' => 'Payment created successfully.',
            'payment' => $payment,
        ];
    }
}

