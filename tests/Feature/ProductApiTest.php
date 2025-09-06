<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists products (paginated)', function () {
    Product::factory()->count(3)->create();

    $response = $this->getJson('/api/products');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'price', 'stock', 'is_active', 'created_at', 'updated_at'],
            ],
            'links',
            'meta',
        ]);
});

it('creates a product', function () {
    $payload = [
        'name' => 'iPhone 42',
        'description' => 'The future phone',
        'price' => 1999.99,
        'stock' => 5,
        'is_active' => true,
    ];

    $response = $this->postJson('/api/products', $payload);

    $response->assertCreated();

    $response->assertJsonFragment([
        'name' => 'iPhone 42',
        'description' => 'The future phone',
        'price' => 1999.99,
        'stock' => 5,
        'is_active' => true,
    ]);

    expect(Product::where('name', 'iPhone 42')->exists())->toBeTrue();
});

it('shows a product', function () {
    $product = Product::factory()->create();

    $this->getJson("/api/products/{$product->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $product->id);
});

it('updates a product', function () {
    $product = Product::factory()->create();

    $this->putJson("/api/products/{$product->id}", [
        'name' => 'Updated',
        'price' => 10,
    ])->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated')
        ->assertJsonPath('data.price', 10);
});

it('deletes a product', function () {
    $product = Product::factory()->create();

    $this->deleteJson("/api/products/{$product->id}")
        ->assertNoContent();

    expect(Product::find($product->id))->toBeNull();
});

it('validates store request', function () {
    $this->postJson('/api/products', [
        'price' => -1,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price']);
});
