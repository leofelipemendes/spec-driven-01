<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection($this->products->paginate($request->validated()));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        return (new ProductResource($this->products->create($request->validated())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $product): ProductResource
    {
        return new ProductResource($this->products->find($product));
    }

    public function update(UpdateProductRequest $request, int $product): ProductResource
    {
        return new ProductResource($this->products->update($product, $request->validated()));
    }

    public function destroy(int $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
