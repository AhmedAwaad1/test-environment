<?php

namespace App\Http\Services\Payment;

use App\Http\Interfaces\Payment\PaymentHandlerInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;


class CardPaymentHandler implements PaymentHandlerInterface
{
    public function pay(Order $order): array
    {
        $secretKey   = config('tap.secret_key');
        $merchantId  = config('tap.merchant_id');
        $redirectUrl = config('tap.redirect_url');
        $webhookUrl  = config('tap.webhook_url');

        // --- MOCK MODE: لو المفاتيح ناقصة نرجّع Redirect داخلي ---
        if (!$secretKey || !$merchantId || !$redirectUrl) {
            $fakeChargeId = 'mock_' . $order->id . '_' . substr(md5($order->order_number), 0, 8);
            // لينك بسيط يكمّل الفلو (success افتراضيًا)
            $mockUrl = url('/api/payments/tap/mock/complete?order_number=' . urlencode($order->order_number) . '&result=success&charge_id=' . $fakeChargeId);

            Log::channel('tap_payments')->info('Tap MOCK redirect issued', [
                'order_id'   => $order->id,
                'order_no'   => $order->order_number,
                'redirect'   => $mockUrl,
                'charge_id'  => $fakeChargeId,
            ]);

            return [
                'success'       => true,
                'message'       => 'Mock redirect to payment',
                'is_redirect'   => true,
                'redirect_url'  => $mockUrl,
                'tap_charge_id' => $fakeChargeId,
            ];
        }

        // --- REAL MODE (Tap) يبقى زي ما شرحنا قبل كده ---
        try {
            $amount   = (float) $order->total_price;
            $currency = $order->currency?->name ?: 'KWD';

            $user    = $order->user;
            $address = $order->address;

            $customer = [
                'first_name' => $user?->first_name ?? 'Guest',
                'last_name'  => $user?->last_name  ?? '',
                'email'      => $user?->email      ?? 'guest@example.com',
                'phone'      => [
                    'country_code' => $address?->country?->phone_code ?? '965',
                    'number'       => $address?->phone ? preg_replace('/\D+/', '', $address->phone) : '50000000',
                ],
            ];

            $reference = [
                'transaction' => 'txn_' . $order->order_number,
                'order'       => $order->order_number,
            ];

            $payload = [
                'amount'             => $amount,
                'currency'           => $currency,
                'threeDSecure'       => true,
                'save_card'          => false,
                'customer_initiated' => true,
                'description'        => 'Order ' . $order->order_number,
                'statement_descriptor' => 'Order ' . $order->order_number,
                'metadata'           => [
                    'order_id' => (string)$order->id,
                    'user_id'  => (string)($user?->id ?? 0),
                ],
                'reference'          => $reference,
                'customer'           => $customer,
                'merchant'           => ['id' => $merchantId],
                'source'             => ['id' => 'src_card'],
                'post'               => ['url' => $webhookUrl],
                'redirect'           => ['url' => $redirectUrl],
            ];

            $client = new Client(['base_uri' => 'https://api.tap.company', 'timeout' => 30]);
            $res    = $client->post('/v2/charges', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $secretKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body          = json_decode((string)$res->getBody(), true);
            $status        = $body['status'] ?? null;
            $chargeId      = $body['id'] ?? null;
            $transactionUrl= $body['transaction']['url'] ?? null;

            if ($status === 'INITIATED' && $transactionUrl) {
                Log::channel('tap_payments')->info('Tap INITIATED', ['order_id' => $order->id, 'charge_id' => $chargeId]);
                return [
                    'success'       => true,
                    'message'       => 'Redirect to Tap',
                    'is_redirect'   => true,
                    'redirect_url'  => $transactionUrl,
                    'tap_charge_id' => $chargeId,
                ];
            }

            if ($status === 'CAPTURED') {
                Log::channel('tap_payments')->info('Tap CAPTURED immediately', ['order_id' => $order->id, 'charge_id' => $chargeId]);
                return [
                    'success'       => true,
                    'message'       => 'Payment captured',
                    'is_redirect'   => false,
                    'redirect_url'  => null,
                    'tap_charge_id' => $chargeId,
                ];
            }

            Log::channel('tap_payments')->warning('Tap charge unexpected status', [
                'order_id' => $order->id,
                'status'   => $status,
                'body'     => $body,
            ]);

            return [
                'success'      => false,
                'message'      => $body['response']['message'] ?? 'Payment failed',
                'is_redirect'  => false,
                'redirect_url' => null,
            ];

        } catch (\Throwable $e) {
            Log::channel('tap_payments')->error('Tap charge error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return [
                'success'      => false,
                'message'      => 'Payment request failed',
                'is_redirect'  => false,
                'redirect_url' => null,
            ];
        }
    }
}
