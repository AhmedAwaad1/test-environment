<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Cart\CartRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class TapMockController extends Controller
{
    public function __construct(protected CartRepository $cartRepo)
    {
    }

    public function complete(Request $request)
    {
        $orderNumber = $request->query('order_number');
        $result      = strtolower($request->query('result', 'success'));
        $chargeId    = $request->query('charge_id', 'mock_' . uniqid());

        if (!$orderNumber) {
            return Response::errorResponse('order_number is required', [], 422);
        }

        $order = Order::with(['orderItems', 'user'])->where('order_number', $orderNumber)->first();

        if (!$order) {
            return Response::errorResponse('order not found', [], 404);
        }

        // Idempotency: لو خلاص اتسجل Captured، رجّع OK على طول
        if ($order->payment_status === 'captured') {
            return Response::successResponse([
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'note'         => 'already captured',
            ], 'already processed');
        }

        if (!in_array($result, ['success','failed'], true)) {
            $result = 'success';
        }

        try {
            DB::beginTransaction();

            if ($result === 'failed') {
                $order->update([
                    'payment_status' => 'failed',
                    // سيب status لسه pending
                ]);

                DB::commit();
                return Response::successResponse([
                    'order_id'       => $order->id,
                    'order_number'   => $order->order_number,
                    'payment_status' => 'failed',
                    'charge_id'      => $chargeId,
                ], 'mock payment failed');
            }

            // success → CAPTURED
            $order->update([
                'payment_status' => 'captured',
                'status'         => 'processing',
            ]);

            // خصم المخزون من الـ order items
            foreach ($order->orderItems as $item) {
                if ($item->product_id) {
                    optional($item->product)->decrement('quantity', (int) $item->quantity);
                }
            }

            // امسح كارت المستخدم لو موجود
            if ($order->user_id) {
                $cart = $this->cartRepo->findUserCart($order->user_id);
                if ($cart) $cart->delete();
            }

            DB::commit();

            return Response::successResponse([
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'payment_status' => 'captured',
                'status'         => $order->status,
                'charge_id'      => $chargeId,
            ], 'mock payment captured');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Mock complete error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return Response::errorResponse('mock complete failed', [], 500);
        }
    }
}
