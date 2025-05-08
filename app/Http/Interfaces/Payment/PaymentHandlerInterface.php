<?php

namespace App\Http\Interfaces\Payment;

use App\Models\Order;

interface PaymentHandlerInterface
{
    public function pay(Order $order): array;
}
