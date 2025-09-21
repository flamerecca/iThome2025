<?php

use App\Features\NewApi;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);

// Categories
Route::apiResource('categories', CategoryController::class);
Route::get('categories/{category}/products', [CategoryController::class, 'products']);

// Tags
Route::apiResource('tags', TagController::class);
Route::get('products/{product}/tags', [TagController::class, 'productTags']);
Route::put('products/{product}/tags', [TagController::class, 'syncProductTags']);
Route::post('products/{product}/tags/{tag}', [TagController::class, 'attachTag']);
Route::delete('products/{product}/tags/{tag}', [TagController::class, 'detachTag']);
Route::get('tags/{tag}/products', [TagController::class, 'tagProducts']);

// Product Images
Route::apiResource('product-images', ProductImageController::class);
Route::get('products/{product}/images', [ProductImageController::class, 'indexByProduct']);
Route::post('products/{product}/images', [ProductImageController::class, 'storeUnderProduct']);
Route::put('product-images/{id}/make-primary', [ProductImageController::class, 'makePrimary']);
Route::patch('products/{product}/images/sort', [ProductImageController::class, 'batchSort']);

//if (Feature::active(NewApi::class)) {
//    Route::get('/legacy', function () {
//        return 'new api';
//    });
//} else {
//    Route::get('/legacy', function () {
//        return 'old api';
//    });
//}
