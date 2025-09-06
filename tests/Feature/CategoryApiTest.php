<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists categories with pagination and filters', function () {
    Category::factory()->count(2)->create(['name' => 'Phone', 'slug' => 'phone']);
    Category::factory()->create(['name' => 'Laptop', 'slug' => 'laptop', 'is_active' => false]);

    $this->getJson('/api/categories?search=phone&is_active=true&sort=-id&per_page=2')
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'description', 'is_active', 'created_at', 'updated_at']], 'links', 'meta'])
        ->assertJsonPath('meta.per_page', 2)
        ->assertJson(fn ($json) => $json->where('data.0.name', 'Phone')->etc());
});

it('creates a category and auto-generates slug when missing', function () {
    $payload = [
        'name' => '中文 名稱',
        'description' => 'desc',
    ];

    $res = $this->postJson('/api/categories', $payload)
        ->assertCreated()
        ->assertJsonPath('data.name', '中文 名稱');

    $slug = data_get($res->json(), 'data.slug');
    expect($slug)->not->toBeNull();
});

it('shows, updates, and deletes a category', function () {
    $category = Category::factory()->create(['name' => 'Phone']);

    $this->getJson("/api/categories/{$category->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $category->id);

    $this->putJson("/api/categories/{$category->id}", [
        'name' => 'Smart Phone',
        'description' => 'updated',
    ])->assertSuccessful()
        ->assertJsonPath('data.name', 'Smart Phone');

    $this->deleteJson("/api/categories/{$category->id}")
        ->assertNoContent();

    expect(Category::find($category->id))->toBeNull();
});

it('validates category store request', function () {
    $this->postJson('/api/categories', [])->assertUnprocessable()->assertJsonValidationErrors(['name']);
});

it('lists products under a category with search/sort', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create(['name' => 'iPhone', 'price' => 999]);
    Product::factory()->for($category)->create(['name' => 'Android', 'price' => 499]);

    $this->getJson("/api/categories/{$category->id}/products?search=phone&sort=-price")
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'name', 'description', 'price', 'stock', 'is_active', 'created_at', 'updated_at']], 'links', 'meta'])
        ->assertJson(fn ($json) => $json->where('data.0.name', 'iPhone')->etc());
});
