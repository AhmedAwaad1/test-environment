<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderRequest;
use App\Http\Services\Admin\Order\OrderService;

class OrderController extends Controller
{
    public $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    public function index(OrderRequest $request)
    {
        return $this->orderService->getAllOrdersForAdmin($request);
    }

    public function show(OrderRequest $request, $id)
    {
        return $this->orderService->getOrderById($id);
    }

    public function update(OrderRequest $request, $id)
    {
        return $this->orderService->updateOrder($id, $request->validated());
    }

}
