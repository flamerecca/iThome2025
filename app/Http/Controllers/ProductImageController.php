<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductImageRequest;
use App\Http\Requests\UpdateProductImageRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductImageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = ProductImage::query();
        if ($productId = request('product_id')) {
            $query->where('product_id', (int) $productId);
        }
        if (! is_null(request('is_active'))) {
            $query->where('is_active', filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
        $sort = request('sort', '-id');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        if (! in_array($column, ['id', 'product_id', 'sort_order', 'created_at', 'updated_at'], true)) {
            $column = 'id';
        }
        $query->orderBy($column, $direction);
        $items = $query->paginate(perPage: request()->integer('per_page', 15));

        return ProductImageResource::collection($items);
    }

    public function store(StoreProductImageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_primary'] = $data['is_primary'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $image = ProductImage::create($data);
        if ($image->is_primary) {
            ProductImage::where('product_id', $image->product_id)
                ->where('id', '!=', $image->id)
                ->update(['is_primary' => false]);
        }

        return (new ProductImageResource($image))->response()->setStatusCode(201);
    }

    public function show(ProductImage $productImage): ProductImageResource
    {
        return new ProductImageResource($productImage);
    }

    public function update(UpdateProductImageRequest $request, ProductImage $productImage): ProductImageResource
    {
        $productImage->update($request->validated());
        if ($productImage->is_primary) {
            ProductImage::where('product_id', $productImage->product_id)
                ->where('id', '!=', $productImage->id)
                ->update(['is_primary' => false]);
        }

        return new ProductImageResource($productImage);
    }

    public function destroy(ProductImage $productImage): \Illuminate\Http\Response
    {
        $productImage->delete();

        return response()->noContent();
    }

    public function indexByProduct(Product $product): AnonymousResourceCollection
    {
        $query = $product->images()->getQuery();
        if (! is_null(request('is_active'))) {
            $query->where('is_active', filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
        $sort = request('sort', '-id');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        if (! in_array($column, ['id', 'sort_order', 'created_at'], true)) {
            $column = 'id';
        }
        $query->orderBy($column, $direction);
        $items = $query->paginate(perPage: request()->integer('per_page', 15));

        return ProductImageResource::collection($items);
    }

    public function storeUnderProduct(Product $product, StoreProductImageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['product_id'] = $product->id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_primary'] = $data['is_primary'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $image = ProductImage::create($data);
        if ($image->is_primary) {
            ProductImage::where('product_id', $image->product_id)
                ->where('id', '!=', $image->id)
                ->update(['is_primary' => false]);
        }

        return (new ProductImageResource($image))->response()->setStatusCode(201);
    }

    public function makePrimary(int $id): ProductImageResource
    {
        $image = ProductImage::findOrFail($id);
        $image->is_primary = true;
        $image->save();
        ProductImage::where('product_id', $image->product_id)
            ->where('id', '!=', $image->id)
            ->update(['is_primary' => false]);

        return new ProductImageResource($image);
    }

    public function batchSort(Product $product): AnonymousResourceCollection
    {
        $data = request()->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:product_images,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);
        foreach ($data['items'] as $item) {
            ProductImage::where('product_id', $product->id)
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
        $items = $product->images()->orderBy('sort_order')->paginate(perPage: request()->integer('per_page', 15));

        return ProductImageResource::collection($items);
    }
}
