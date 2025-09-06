<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = Category::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        if (! is_null(request('is_active'))) {
            $query->where('is_active', filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
        $sort = request('sort', '-id');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        if (! in_array($column, ['id', 'name', 'created_at', 'updated_at'], true)) {
            $column = 'id';
        }
        $query->orderBy($column, $direction);

        $categories = $query->paginate(perPage: request()->integer('per_page', 15));

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $data['is_active'] = $data['is_active'] ?? true;
        $category = Category::create($data);

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category): \Illuminate\Http\Response
    {
        $category->delete();

        return response()->noContent();
    }

    public function products(Category $category): AnonymousResourceCollection
    {
        $query = $category->products()->getQuery();
        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if (! is_null(request('is_active'))) {
            $query->where('is_active', filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
        $sort = request('sort', '-id');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        if (! in_array($column, ['id', 'name', 'price', 'created_at'], true)) {
            $column = 'id';
        }
        $query->orderBy($column, $direction);
        $products = $query->paginate(perPage: request()->integer('per_page', 15));

        return \App\Http\Resources\ProductResource::collection($products);
    }
}
