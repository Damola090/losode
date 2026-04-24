<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Vendor;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function listPublicProducts(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getActiveProducts($search, $perPage);
    }

    public function showPublicProduct(int $id): Product
    {
        $product = $this->productRepository->findActiveById($id);

        if (! $product) {
            throw new NotFoundHttpException('Product not found.');
        }

        return $product;
    }

    public function listVendorProducts(Vendor $vendor, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getVendorProducts($vendor->id, $perPage);
    }

    public function createProduct(Vendor $vendor, array $data): Product
    {
        return $this->productRepository->create(array_merge($data, ['vendor_id' => $vendor->id]));
    }

    public function updateProduct(Vendor $vendor, int $productId, array $data): Product
    {
        $product = $this->findOwnedProduct($vendor, $productId);

        return $this->productRepository->update($product, $data);
    }

    public function deleteProduct(Vendor $vendor, int $productId): void
    {
        $product = $this->findOwnedProduct($vendor, $productId);

        $this->productRepository->delete($product);
    }

    private function findOwnedProduct(Vendor $vendor, int $productId): Product
    {
        $product = $this->productRepository->findById($productId);

        if (! $product) {
            throw new NotFoundHttpException('Product not found.');
        }

        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'You do not own this product.');
        }

        return $product;
    }
}
