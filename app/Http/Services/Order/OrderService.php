<?php

namespace App\Http\Services\Order;

use App\Events\OrderPlaced;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Repositories\Order\OrderRepository;
use App\Actions\Order\ResolveUserAction;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\ProcessPaymentAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepo,
        protected ResolveUserAction $resolveUserAction,
        protected CreateOrderAction $createOrderAction,
        protected ProcessPaymentAction $processPaymentAction,
        protected GeoCurrencyService $geoCurrencyService
    ) {}

    public function getAllUserOrders($request)
    {
        $user               = auth()->user();
        $request['user_id'] = $user->id;
        $query              = $this->orderRepo->getAllUserOrder($request);

        if ($request->per_page) {
            $orders = new PaginationResource($query->paginate($request->per_page), OrderResource::class);
        } else {
            $orders = OrderResource::collection($query->get());
        }

        return Response::successResponse($orders, 'orders retrieved successfully');
    }

    public function getOrderById($id)
    {
        try {
            $order = $this->orderRepo->findOrderById($id);

            if (!$order) {
                return Response::errorResponse('order not found', [], 404);
            }

            return Response::successResponse(new OrderResource($order), 'order found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'order');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve order');
        }
    }

    public function checkout($request)
    {
        try {
            // 1. Resolve Identity (Auth or Guest) & Cart Migration
            $identity = $this->resolveUserAction->execute($request);
            $user = $identity['user'];
            $token = $identity['token'];

            // 1.1 Get Cart before transaction (to avoid issues with observers clearing it)
            $cart = $this->orderRepo->getCartForOrder($user->id);
            if ($cart) {
                $cart->load(['cartItems.productVariant', 'cartItems.product', 'cartItems.productSetItem']);
            }

            // 2. Atomic Orchestration
            return DB::transaction(function () use ($user, $request, $token, $cart) {
                // 2.1 Create Order & Items (Includes stock validation with row-level locks)
                $order = $this->createOrderAction->execute($user, $request);

                // 2.2 Process Payment
                $paymentResult = $this->processPaymentAction->execute($order, $request);

                // 2.3 Fire OrderPlaced Event (Crucial: Inside transaction for stock atomicity)
                event(new OrderPlaced($order, $cart));

                // 2.4 Prepare Response
                $order->load(['orderItems', 'currency', 'address.country', 'address.city', 'address.district']);
                
                if ($paymentResult['is_redirect'] ?? false) {
                    $order->update(['tap_charge_id' => $paymentResult['tap_charge_id'] ?? null]);
                    
                    $payload = [
                        'order'        => new OrderResource($order),
                        'redirect_url' => $paymentResult['redirect_url'] ?? null,
                    ];
                } else {
                    $payload = new OrderResource($order);
                }

                if ($token) {
                    $payload = is_array($payload) ? array_merge($payload, ['token' => $token]) : ['order' => $payload, 'token' => $token];
                }

                return Response::successResponse(
                    $payload, 
                    ($paymentResult['is_redirect'] ?? false) ? 'redirect to payment' : 'order created successfully', 
                    201
                );
            });

        } catch (\App\Exceptions\InsufficientStockException $e) {
            return Response::errorResponse($e->getMessage(), [], 422);
        } catch (\App\Exceptions\PriceMismatchException $e) {
            return Response::errorResponse($e->getMessage(), [], 422);
        } catch (\App\Exceptions\PaymentFailedException $e) {
            return Response::errorResponse($e->getMessage(), [], 400);
        } catch (\Exception $e) {
            return Response::handleException($e, 'checkout');
        }
    }
}
