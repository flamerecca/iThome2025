<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists product images with filters and sort', function () {
    $p1 = Product::factory()->create();
    ProductImage::factory()->for($p1)->create(['is_active' => true, 'sort_order' => 2]);
    ProductImage::factory()->for($p1)->create(['is_active' => false, 'sort_order' => 1]);

    $this->getJson('/api/product-images?product_id='.$p1->id.'&is_active=true&sort=sort_order')
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'product_id', 'url', 'alt', 'is_primary', 'sort_order', 'is_active', 'created_at', 'updated_at']], 'links', 'meta'])
        ->assertJsonPath('data.0.is_active', true);
});

it('creates/updates/deletes product images and enforces single primary', function () {
    $product = Product::factory()->create();

    $res1 = $this->postJson('/api/product-images', [
        'product_id' => $product->id,
        'url' => 'https://example.com/a.jpg',
        'is_primary' => true,
    ])->assertCreated();
    $img1 = data_get($res1->json(), 'data');

    $res2 = $this->postJson('/api/product-images', [
        'product_id' => $product->id,
        'url' => 'https://example.com/b.jpg',
        'is_primary' => true,
    ])->assertCreated();
    $img2 = data_get($res2->json(), 'data');

    // now img1 should be not primary
    $this->getJson('/api/product-images/'.$img1['id'])
        ->assertSuccessful()
        ->assertJsonPath('data.is_primary', false);

    // update second to non-primary
    $this->patchJson('/api/product-images/'.$img2['id'], ['is_primary' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.is_primary', false);

    // delete
    $this->deleteJson('/api/product-images/'.$img1['id'])->assertNoContent();
});

it('supports nested endpoints under product and behaviors', function () {
    $product = Product::factory()->create();

    // create under product
    $res = $this->postJson('/api/products/'.$product->id.'/images', [
        'url' => 'https://example.com/c.jpg',
        'is_primary' => true,
    ])->assertCreated();

    $imgId = data_get($res->json(), 'data.id');

    // list under product
    $this->getJson('/api/products/'.$product->id.'/images?sort=-id')
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'product_id', 'url', 'alt', 'is_primary', 'sort_order', 'is_active', 'created_at', 'updated_at']], 'links', 'meta']);

    // make primary endpoint
    $this->putJson('/api/product-images/'.$imgId.'/make-primary')
        ->assertSuccessful()
        ->assertJsonPath('data.is_primary', true);

    // batch sort
    $i2 = ProductImage::factory()->for($product)->create();
    $this->patchJson('/api/products/'.$product->id.'/images/sort', [
        'items' => [
            ['id' => $imgId, 'sort_order' => 5],
            ['id' => $i2->id, 'sort_order' => 1],
        ],
    ])->assertSuccessful();
});
