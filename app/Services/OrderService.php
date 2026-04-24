<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Place an order atomically.
     *
     * Uses a DB transaction + a conditional UPDATE (stock >= quantity)
     * to prevent overselling even under concurrent requests.
     */
    public function placeOrder(array $data): Order
    {
        $product = $this->productRepository->findActiveById($data['product_id']);

        if (! $product) {
            throw new NotFoundHttpException('Product not found.');
        }

        return DB::transaction(function () use ($product, $data) {
            // Atomic decrement — fails silently if stock is insufficient
            $decremented = $this->productRepository->decrementStock($product, $data['quantity']);

            if (! $decremented) {
                throw new UnprocessableEntityHttpException(
                    "Insufficient stock. Available: {$product->stock_quantity}."
                );
            }

            return $this->orderRepository->create([
                'product_id'     => $product->id,
                'customer_name'  => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'quantity'       => $data['quantity'],
                'unit_price'     => $product->price,
                'total_price'    => $product->price * $data['quantity'],
                'status'         => 'confirmed',
            ]);
        });
    }
}
