<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TapWebhookController extends Controller
{
    /**
     * Handles the asynchronous POST request from Tap Payments.
     */
    public function handleWebhook(Request $request)
    {
        // 1. --- SECURITY: Tap Signature Verification ---
        if (!$this->verifyTapSignature($request)) {
            Log::channel('tap_payments')->error('Tap Webhook: SECURITY FAILURE - Invalid Signature', [
                'headers' => $request->headers->all(),
                'body_length' => strlen($request->getContent())
            ]);
            return response()->json(['message' => 'Unauthorized: Invalid Signature'], 403);
        }

        $payload = $request->json()->all();
        $chargeId = $payload['id'] ?? null;
        $status = $payload['status'] ?? null;
        $reference = $payload['reference']['order'] ?? null;

        Log::channel('tap_payments')->info('Tap Webhook received', [
            'charge_id' => $chargeId,
            'status' => $status,
            'reference' => $reference
        ]);

        // Guard: Make sure we have the essential data
        if (!$chargeId || !$status) {
            return response()->json(['message' => 'Missing ID or Status'], 400);
        }

        // 2. --- Find the Order in the Database ---
        $order = Order::where('tap_charge_id', $chargeId)->first();

        if (!$order) {
            Log::channel('tap_payments')->error('Tap Webhook: Order not found for Charge ID', [
                'charge_id' => $chargeId
            ]);
            return response()->json(['message' => 'Order not found, but acknowledged'], 200);
        }

        // Guard: Prevent double-processing (idempotency)
        if ($order->payment_status === 'paid' || $order->payment_status === 'captured') {
            Log::channel('tap_payments')->info('Tap Webhook: Order already processed', [
                'order_id' => $order->id,
                'current_status' => $order->payment_status
            ]);
            return response()->json(['message' => 'Order already processed'], 200);
        }

        // 3. --- Process the Status ---
        DB::beginTransaction();
        try {
            if ($status === 'CAPTURED' || $status === 'PAID') {

                // Update Order Status
                $order->update([
                    'payment_status' => 'captured',
                    'status' => 'processing',
                ]);

                // Deduct Stock and Delete Cart
                $order->orderItems->each(function ($item) {
                    if ($item->product_id) {
                        $item->product?->decrement('quantity', (int)$item->quantity);
                    }
                });

                // Delete the User's Cart
                $order->user?->cart?->delete();

                Log::channel('tap_payments')->info('Tap Webhook: Payment CAPTURED and order updated.', [
                    'order_id' => $order->id
                ]);

            } elseif ($status === 'FAILED' || $status === 'CANCELLED' || $status === 'DECLINED') {

                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);

                Log::channel('tap_payments')->warning('Tap Webhook: Payment FAILED.', [
                    'order_id' => $order->id,
                    'final_status' => $status
                ]);

            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('tap_payments')->error('Tap Webhook: Transaction failed during DB update.', [
                'charge_id' => $chargeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }

        // 4. --- Acknowledge Receipt ---
        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }

    /**
     * Verifies the authenticity of the Tap Webhook using hashstring validation.
     *
     * According to Tap's official documentation:
     * https://developers.tap.company/docs/webhook
     *
     * The hashstring is calculated using specific fields from the payload,
     * NOT the raw JSON body.
     */
    private function verifyTapSignature(Request $request): bool
    {
        $hashstring = $request->header('hashstring');

        if (!$hashstring) {
            Log::channel('tap_payments')->error('Signature Verification: No hashstring header found');
            return false;
        }

        $payload = $request->json()->all();
        $secretKey = config('tap.secret_key');

        if (empty($secretKey)) {
            Log::channel('tap_payments')->error('Signature Verification: Secret key not configured');
            return false;
        }

        // Extract required fields from payload
        $id = $payload['id'] ?? '';
        $amount = $payload['amount'] ?? 0;
        $currency = $payload['currency'] ?? '';
        $gatewayReference = $payload['reference']['gateway'] ?? '';
        $paymentReference = $payload['reference']['payment'] ?? '';
        $status = $payload['status'] ?? '';
        $created = $payload['transaction']['created'] ?? '';

        // Format amount with correct decimal places based on currency (ISO standard)
        $amount = $this->formatAmount($amount, $currency);

        // Build the hashstring according to Tap's specification
        // For Charges/Authorize: x_id{id}x_amount{amount}x_currency{currency}x_gateway_reference{ref}x_payment_reference{ref}x_status{status}x_created{created}
        $toBeHashed = 'x_id' . $id .
            'x_amount' . $amount .
            'x_currency' . $currency .
            'x_gateway_reference' . $gatewayReference .
            'x_payment_reference' . $paymentReference .
            'x_status' . $status .
            'x_created' . $created;

        // Calculate the HMAC-SHA256 hash
        $calculatedHash = hash_hmac('sha256', $toBeHashed, $secretKey);

        $isValid = hash_equals($calculatedHash, $hashstring);

        if (!$isValid) {
            Log::channel('tap_payments')->warning('Signature Verification: Hash mismatch', [
                'calculated_hash' => substr($calculatedHash, 0, 20) . '...',
                'received_hash' => substr($hashstring, 0, 20) . '...',
                'string_to_hash' => $toBeHashed
            ]);
        } else {
            Log::channel('tap_payments')->info('Signature Verification: SUCCESS');
        }

        return $isValid;
    }

    /**
     * Format amount with correct decimal places based on currency (ISO standard)
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    private function formatAmount(float $amount, string $currency): string
    {
        // Currencies with 3 decimal places
        $threeDecimalCurrencies = ['BHD', 'KWD', 'OMR', 'JOD'];

        if (in_array(strtoupper($currency), $threeDecimalCurrencies)) {
            return number_format($amount, 3, '.', '');
        }

        // All other currencies use 2 decimal places (AED, SAR, QAR, USD, EUR, GBP, EGP, etc.)
        return number_format($amount, 2, '.', '');
    }
}
