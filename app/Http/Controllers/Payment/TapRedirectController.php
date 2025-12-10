<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TapRedirectController extends Controller
{
    /**
     * Handles the GET request when the user is redirected back from Tap.
     */
    public function handleRedirect(Request $request)
    {
        // Tap sends parameters in the query string upon redirect
        $tapId = $request->query('tap_id'); // This is the charge ID
        $result = $request->query('result'); // Payment status (success, failure, etc.)
        $reference = $request->query('ref'); // The transaction reference (e.g., your order number)

        Log::channel('tap_payments')->info('Tap Redirect received', [
            'tap_id' => $tapId,
            'result' => $result,
            'reference' => $reference,
        ]);

        if (!$tapId) {
            return view('payment.failure', ['message_for_user' => 'Payment ID missing in redirect.']);
        }

        // 1. Find the Order
        $order = Order::where('tap_charge_id', $tapId)->first();

        if (!$order) {
            Log::channel('tap_payments')->warning('Redirect: Order not found for Tap ID', ['tap_id' => $tapId]);
            return view('payment.failure', ['message_for_user' => 'Order not found or payment not initiated.']);
        }

        // 2. Return the status to the frontend
        // We look at the order's DB status which *should* be updated by the Webhook (Step 2)
        // If the Webhook hasn't arrived yet, we use the temporary result from the query string.
        
        $finalStatus = $order->payment_status;

        // If payment_status is still 'pending' (Webhook hasn't arrived yet),
        // we use the 'result' from the query for immediate user feedback.
        if ($finalStatus === 'pending') {
            if ($result === 'success') {
                return view('payment.success', ['order' => $order->load('currency', 'address', 'orderItems')]);
            } elseif ($result === 'failure') {
                return view('payment.failure', ['order' => $order->load('currency', 'address', 'orderItems'), 'message_for_user' => 'Payment failed. Please try again.']);
            } else {
                return view('payment.failure', ['order' => $order->load('currency', 'address', 'orderItems'), 'message_for_user' => 'Payment processing status is unknown. Please check your order history.']);
            }
        } else {
            // Webhook has already arrived and updated the order
            if ($finalStatus === 'paid' || $finalStatus === 'captured') {
                return view('payment.success', ['order' => $order->load('currency', 'address', 'orderItems')]);
            } else {
                return view('payment.failure', ['order' => $order->load('currency', 'address', 'orderItems'), 'message_for_user' => 'Payment status: ' . $finalStatus]);
            }
        }
    }
}