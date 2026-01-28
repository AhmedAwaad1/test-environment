<?php

namespace App\Http\Services\Admin\Order;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\Address\AddressRepository;
use App\Repositories\Admin\Order\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class OrderService
{
    protected $orderRepo;
    protected $addressRepo;

    public function __construct(OrderRepository $orderRepo, AddressRepository $addressRepo)
    {
        $this->orderRepo = $orderRepo;
        $this->addressRepo = $addressRepo;
    }

    public function getAllOrdersForAdmin($request)
    {
        $query = $this->orderRepo->getAllOrdersForAdmin($request);

        if ($request->per_page) {
            $orders = new PaginationResource($query->paginate($request->per_page), OrderResource::class);
        } else {
            $orders = OrderResource::collection($query->get());
        }

        return Response::successResponse($orders, 'Orders retrieved successfully');
    }

    public function getOrderById($id)
    {
        $order = $this->orderRepo->findOrderById($id);

        if (!$order) {
            return Response::errorResponse('Order not found', [], 404);
        }

        return Response::successResponse(new OrderResource($order), 'Order found successfully');
    }

    public function updateOrder($id, $data)
    {
        $order = $this->orderRepo->updateOrderById($id, $data);

        return Response::successResponse(new OrderResource($order), 'Order updated successfully');
    }
}

