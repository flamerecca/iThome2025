<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $products = Product::query()
            ->latest('id')
            ->paginate(perPage: request()->integer('per_page', 15));

        return \App\Http\Resources\ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $data['stock'] = $data['stock'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        $product = Product::create($data);

        return (new \App\Http\Resources\ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): \App\Http\Resources\ProductResource
    {
        return new \App\Http\Resources\ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): \App\Http\Resources\ProductResource
    {
        $product->update($request->validated());

        return new \App\Http\Resources\ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): \Illuminate\Http\Response
    {
        $product->delete();

        return response()->noContent();
    }
}
