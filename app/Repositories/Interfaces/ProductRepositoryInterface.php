<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getActiveProducts(?string $search, int $perPage): LengthAwarePaginator;

    public function getVendorProducts(int $vendorId, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function findActiveById(int $id): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    public function decrementStock(Product $product, int $quantity): bool;
}
