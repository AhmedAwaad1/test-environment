<?php

namespace App\Http\Services\Order;

use App\Events\OrderPlaced;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Services\Cart\ProductValidatorService;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\Payment\PaymentFactoryService;
use App\Models\Country;
use App\Repositories\Address\AddressRepository;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\UserRepository\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderService
{
    protected $orderRepo, $cartRepo, $paymentFactory, $addressRepo, $orderItemRepo, $productValidator, $userRepo;

    public function __construct(
        OrderRepository         $orderRepo,
        OrderItemRepository     $orderItemRepo,
        CartRepository          $cartRepo,
        AddressRepository       $addressRepo,
        PaymentFactoryService   $paymentFactory,
        ProductValidatorService $productValidatorService,
        UserRepository          $userRepo
    )

    {
        $this->cartRepo         = $cartRepo;
        $this->paymentFactory   = $paymentFactory;
        $this->orderRepo        = $orderRepo;
        $this->addressRepo      = $addressRepo;
        $this->orderItemRepo    = $orderItemRepo;
        $this->productValidator = $productValidatorService;
        $this->userRepo         = $userRepo;

    }

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
            $user  = auth()->user();
            $order = $this->orderRepo->findOrderById($id);

            if (!$order) {
                return Response::errorResponse('order not found', [], 404);
            }

            return Response::successResponse(new OrderResource($order), 'order found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'order');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve order');
        }
    }

    public function checkout($request)
    {
        $isGuest = false;
        $user = auth()->user();

        if (!$user) {
            $user = $this->userRepo->getOrCreateGuestUser($request);
            $isGuest = true;

            if (!empty($request['session_id'])) {
                $guestCart = $this->cartRepo->findBySessionId($request['session_id']);
                if ($guestCart) {
                    $guestCart->update([
                        'user_id'    => $user->id,
                        'session_id' => null,
                    ]);
                }
            }

            $token = JWTAuth::fromUser($user);
        }

        $cart = $this->cartRepo->findUserCart($user->id);

        if (!$cart || $cart->cartItems->isEmpty()) {
            return Response::errorResponse('cart is empty', [], 404);
        }

        $orderNumber = $this->generateOrderNumber();

        $userAddress = $this->addressRepo->handleCheckoutAddress($user->id, $request);

        if (!$userAddress) {
            return Response::errorResponse('address not found', [], 404);
        }

        $shippingPrice = (float) $userAddress->getShippingPrice();

        $request = array_merge($request, [
            'order_number'   => $orderNumber,
            'user_id'        => $user->id,
            'shipping_price' => $shippingPrice,
            'address_id'     => $userAddress->id,
        ]);

        DB::beginTransaction();

        try {
            $productValidation = $this->validateProductsAndStock($cart->cartItems);
            if (is_array($productValidation) && isset($productValidation['error'])) {
                DB::rollBack();
                return Response::errorResponse($productValidation['error'], [], 422);
            }

            $order = $this->orderRepo->createOrder($request, $cart);

            $this->orderItemRepo->createOrderItems($order->id, $cart->cartItems);

            $paymentMethod  = $request['payment_method'];
            $paymentHandler = $this->paymentFactory->make($paymentMethod);

            if (!$paymentHandler) {
                DB::rollBack();
                return Response::errorResponse('unsupported payment method', [], 400);
            }

            $paymentResult  = $paymentHandler->pay($order);

            if (!($paymentResult['success'] ?? false)) {
                DB::rollBack();
                return Response::errorResponse($paymentResult['message'] ?? 'payment failed', [], 400);
            }

            // لو Redirect: منخصمش ستوك ولا نمسح الكارت، ونرجّع redirect_url
            if ($paymentResult['is_redirect'] ?? false) {
                
                // 👇 CRITICAL ADJUSTMENT: Store the Charge ID before committing 
                $order->update([
                    'tap_charge_id' => $paymentResult['tap_charge_id'] ?? null,
                ]);
                
                DB::commit();

                // Fire event for stock and cart cleanup
                event(new OrderPlaced($order, $cart));

                $responseData = new OrderResource(
                    $order->load(['orderItems', 'currency', 'address.country', 'address.city', 'address.district'])
                );

                $payload = [
                    'order'        => $responseData,
                    'redirect_url' => $paymentResult['redirect_url'] ?? null,
                ];
                if (isset($token)) {
                    $payload['token'] = $token;
                }

                return Response::successResponse($payload, 'redirect to payment', 201);
            }

            // لو الدفع ناجح فورًا (CAPTURED): نكمّل زي المعتاد
            DB::commit();

            // Fire event for stock and cart cleanup
            event(new OrderPlaced($order, $cart));

            $responseData = new OrderResource(
                $order->load(['orderItems', 'currency', 'address.country', 'address.city', 'address.district'])
            );

            if (isset($token)) {
                $responseData = [
                    'order' => $responseData,
                    'token' => $token,
                ];
            }

            return Response::successResponse($responseData, 'order created successfully', 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return Response::handleModelNotFoundException($e, 'order');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to retrieve order');
        }
    }


    private function generateOrderNumber(): string
    {
        $prefix       = 'ORD-';
        $randomNumber = mt_rand(100000, 999999);
        $timestamp    = now()->format('YmdHis');

        return $prefix . $timestamp . '-' . $randomNumber;
    }

    protected function validateProductsAndStock($cartItems)
    {
        foreach ($cartItems as $item) {
            if ($item->product_variant_id) {
                $validation = $this->productValidator->validateVariant($item->productVariant, $item->quantity);
            } elseif ($item->product_id) {
                $validation = $this->productValidator->validateProduct($item->product, $item->quantity);
            } elseif ($item->product_set_item_id) {
                $validation = $this->productValidator->validateProductSetItem($item->productSetItem, $item->quantity);
            } else {
                $validation = 'Product not found';
            }

            if ($validation !== true) {
                Log::error("Stock validation failed", [
                    'user_id'            => auth()->id(),
                    'product_or_variant' => $item->product_variant_id ? 'variant' : ($item->product_id ? 'product' : 'set_item'),
                    'product_id'         => $item->product_variant_id ?? ($item->product_id ?? $item->product_set_item_id),
                    'reason'             => $validation
                ]);
                
                $product = null;
                if ($item->product_variant_id) {
                    $product = $item->productVariant;
                } elseif ($item->product_id) {
                    $product = $item->product;
                } elseif ($item->product_set_item_id) {
                    $product = $item->productSetItem;
                }

                return ['error' => $validation, 'product' => $product];
            }

        }
        return true;
    }

}
