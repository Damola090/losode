<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    // ─── Public endpoints ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->listPublicProducts(
            $request->query('search'),
            (int) $request->query('per_page', 15)
        );

        return $this->successResponse('Products retrieved.', $products);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->showPublicProduct($id);

        return $this->successResponse('Product retrieved.', ['product' => $product]);
    }

    // ─── Vendor-only endpoints ───────────────────────────────────────────────

    public function vendorIndex(Request $request): JsonResponse
    {
        $products = $this->productService->listVendorProducts(
            $request->user(),
            (int) $request->query('per_page', 15)
        );

        return $this->successResponse('Your products retrieved.', $products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse('Product created.', ['product' => $product], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->updateProduct(
            $request->user(),
            $id,
            $request->validated()
        );

        return $this->successResponse('Product updated.', ['product' => $product]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->productService->deleteProduct($request->user(), $id);

        return $this->successResponse('Product deleted.');
    }
}
