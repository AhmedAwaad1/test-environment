<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderRequest;
use App\Http\Services\Order\OrderService;

class OrderController extends Controller
{
    public $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(OrderRequest $request)
    {
        return $this->orderService->getAllUserOrders($request);
    }

    public function show(OrderRequest $request)
    {
        return $this->orderService->getOrderById($request->id);
    }

    public function checkout(OrderRequest $request)
    {
        return $this->orderService->checkout($request->validated());
    }

}
