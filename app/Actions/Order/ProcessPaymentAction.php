<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Http\Services\Payment\PaymentFactoryService;
use App\Exceptions\PaymentFailedException;

class ProcessPaymentAction
{
    public function __construct(
        protected PaymentFactoryService $paymentFactory
    ) {}

    public function execute(Order $order, array $request): array
    {
        $paymentMethod = $request['payment_method'] ?? 'cod';
        $paymentHandler = $this->paymentFactory->make($paymentMethod);

        if (!$paymentHandler) {
            throw new \Exception('Unsupported payment method');
        }

        $paymentResult = $paymentHandler->pay($order);

        if (!($paymentResult['success'] ?? false)) {
            throw new PaymentFailedException($paymentResult['message'] ?? 'Payment failed');
        }

        return $paymentResult;
    }
}
