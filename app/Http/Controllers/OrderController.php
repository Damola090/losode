<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\PlaceOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->placeOrder($request->validated());

        return $this->successResponse('Order placed successfully.', ['order' => $order], 201);
    }
}
