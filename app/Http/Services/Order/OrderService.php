<?php

namespace App\Http\Services\Order;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Services\Cart\ProductValidatorService;
use App\Http\Services\Payment\PaymentFactoryService;
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
        $this->userRepo = $userRepo;

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

        $orderNumber = $this->generateOrderNumber();

        if (!$isGuest) {
            $userAddress = $this->addressRepo->find($request['address_id'], $user->id);

            if (!$userAddress) {
                return Response::errorResponse('address not found', [], 404);
            }
        } else {
            $userAddress = $this->addressRepo->create([
                'user_id'     => $user->id,
                'phone'       => $user->phone,
                'address'     => $request['address'],
                'city_id'     => $request['city_id'] ?? null,
                'district_id' => $request['district_id'] ?? null,
                'is_default'  => $request['is_default'] ?? false,
            ]);
        }


        $shippingPrice = $userAddress->getShippingPrice();

        $request = array_merge($request, [
            'order_number'   => $orderNumber,
            'user_id'        => $user->id,
            'shipping_price' => $shippingPrice,
            'address_id'     => $userAddress->id,
        ]);


        if (!$cart || $cart->cartItems->isEmpty()) {
            return Response::errorResponse('cart is empty', [], 404);
        }

        DB::beginTransaction();

        try {
            $productValidation = $this->validateProductsAndStock($cart->cartItems);

            $order      = $this->orderRepo->createOrder($request, $cart);
            $orderItems = $this->orderItemRepo->createOrderItems($order->id, $cart->cartItems);

            $paymentMethod  = $request['payment_method'];
            $paymentHandler = $this->paymentFactory->make($paymentMethod);
            $paymentResult  = $paymentHandler->pay($order);

            if (!$paymentResult['success']) {
                DB::rollBack();
                return Response::errorResponse($paymentResult['message'], [], 400);
            }

            foreach ($cart->cartItems as $item) {
                if ($item->product_id) {
                    $item->product->decrement('quantity', $item->quantity);
                } else {
                    $item->productVariant->decrement('quantity', $item->quantity);
                }
            }

            DB::commit();
            $cart->delete();


            $responseData = new OrderResource($order->load('orderItems'));

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
            if ($item->product_id) {
                $validation = $this->productValidator->validateProduct($item->product, $item->quantity);
            } else {
                $validation = $this->productValidator->validateVariant($item->productVariant, $item->quantity);
            }

            if ($validation !== true) {
                Log::error("Stock validation failed", [
                    'user_id'            => auth()->id(),
                    'product_or_variant' => $item->product_id ? 'product' : 'variant',
                    'product_id'         => $item->product_id ?? $item->product_variant_id,
                    'reason'             => $validation
                ]);
                return ['error' => $validation, 'product' => $item->product_id ? $item->product : $item->productVariant];
            }

        }
        return true;
    }

}
