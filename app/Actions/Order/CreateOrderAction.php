<?php

namespace App\Actions\Order;

use App\Models\User;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Address\AddressRepository;
use App\Http\Services\Cart\ProductValidatorService;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\PriceMismatchException;
use Illuminate\Support\Facades\Log;

class CreateOrderAction
{
    public function __construct(
        protected OrderRepository $orderRepo,
        protected OrderItemRepository $orderItemRepo,
        protected CartRepository $cartRepo,
        protected AddressRepository $addressRepo,
        protected ProductValidatorService $productValidator
    ) {}

    public function execute(User $user, array $request): \App\Models\Order
    {
        $cart = $this->cartRepo->findUserCart($user->id);

        if (!$cart || $cart->cartItems->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        // 1. Fresh Price Sync & Validation
        $this->syncPricesAndValidate($cart->cartItems);

        // 2. Validate products and stock (with atomic locks)
        $this->validateProductsAndStock($cart->cartItems);

        // Calculate final subtotal and total after product discount from fresh prices
        $freshSubtotal = $cart->cartItems->sum(fn($item) => $item->unit_price * $item->quantity);
        $freshTotalAfterProdDisc = $cart->cartItems->sum(fn($item) => ($item->unit_price_after_discount ?? $item->unit_price) * $item->quantity);

        $cart->total_price = $freshSubtotal; 
        $cart->total_price_after_discount = $freshTotalAfterProdDisc;

        $orderNumber = $this->generateOrderNumber();
        $userAddress = $this->addressRepo->handleCheckoutAddress($user->id, $request);

        if (!$userAddress) {
            throw new \Exception('Shipping address not found');
        }

        $shippingPrice = (float) $userAddress->getShippingPrice();

        // Calculate final total from fresh prices (including coupon and shipping)
        $couponDiscount = (float)($cart->discount_amount ?? 0);
        $totalPrice = $freshTotalAfterProdDisc - $couponDiscount + $shippingPrice;

        $orderData = array_merge($request, [
            'order_number'   => $orderNumber,
            'user_id'        => $user->id,
            'shipping_price' => $shippingPrice,
            'address_id'     => $userAddress->id,
            'total_price'    => $totalPrice,
        ]);

        $order = $this->orderRepo->createOrder($orderData, $cart);
        $this->orderItemRepo->createOrderItems($order->id, $cart->cartItems);

        return $order;
    }

    protected function syncPricesAndValidate($cartItems)
    {
        foreach ($cartItems as $item) {
            $freshPrice = 0;
            $freshDiscountedPrice = null;
            $productName = '';

            if ($item->product_variant_id) {
                $variant = $item->productVariant;
                $freshPrice = (float) $variant->price;
                $freshDiscountedPrice = $variant->price_after_discount !== null ? (float) $variant->price_after_discount : null;
                $productName = $variant->product->name_en . ' (' . $variant->getTitle() . ')';
            } elseif ($item->product_id) {
                $product = $item->product;
                $priceEntry = $product->productPrices()->where('currency_id', $item->currency_id)->first();
                if (!$priceEntry) {
                    throw new \Exception("Price not found for product: {$product->name_en}");
                }
                $freshPrice = (float) $priceEntry->price;
                $freshDiscountedPrice = $priceEntry->price_after_discount !== null ? (float) $priceEntry->price_after_discount : null;
                $productName = $product->name_en;
            } elseif ($item->product_set_item_id) {
                $setItem = $item->productSetItem;
                $prices = $setItem->getTotalPriceForCurrency($item->currency_id, $item->selected_product_ids);
                $freshPrice = (float) $prices['price'];
                $freshDiscountedPrice = (float) $prices['price_after_discount'];
                $productName = $setItem->name_en;
            }

            $currentUnitPrice = $freshDiscountedPrice ?? $freshPrice;
            $cartUnitPrice = (float) ($item->unit_price_after_discount ?? $item->unit_price);

            if (abs($currentUnitPrice - $cartUnitPrice) > 0.01) {
                throw new PriceMismatchException("Price for [{$productName}] has changed. Please review your cart.");
            }

            // Update item in memory for snapshotting
            $item->unit_price = $freshPrice;
            $item->unit_price_after_discount = $freshDiscountedPrice;
            $item->total_price = round($freshPrice * $item->quantity, 2);
            $item->product_name_snapshot = $productName; // 👈 Attach name for Repository
        }
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
                Log::error("Stock validation failed during checkout", [
                    'user_id' => auth()->id(),
                    'item' => $item->id,
                    'reason' => $validation
                ]);
                throw new InsufficientStockException($validation);
            }
        }
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('YmdHis') . '-' . mt_rand(100000, 999999);
    }
}
