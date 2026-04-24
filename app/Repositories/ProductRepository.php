<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function getActiveProducts(?string $search, int $perPage): LengthAwarePaginator
    {
        return Product::active()
            ->with('vendor:id,name')
            ->when($search, fn ($q) => $q->search($search))
            ->latest()
            ->paginate($perPage);
    }

    public function getVendorProducts(int $vendorId, int $perPage): LengthAwarePaginator
    {
        return Product::where('vendor_id', $vendorId)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::with('vendor:id,name')->find($id);
    }

    public function findActiveById(int $id): ?Product
    {
        return Product::active()->with('vendor:id,name')->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Atomically decrement stock using a DB-level constraint to prevent negative values.
     * Uses a WHERE clause guard so concurrent requests cannot both succeed on the last unit.
     */
    public function decrementStock(Product $product, int $quantity): bool
    {
        $affected = Product::where('id', $product->id)
            ->where('stock_quantity', '>=', $quantity)
            ->update([
                'stock_quantity' => \DB::raw("stock_quantity - {$quantity}"),
            ]);

        return $affected > 0;
    }
}
