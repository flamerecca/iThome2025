<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists, creates, shows, updates, deletes tags', function () {
    Tag::factory()->count(2)->create(['name' => 'sale']);
    Tag::factory()->create(['name' => 'summer', 'is_active' => false]);

    $this->getJson('/api/tags?search=sale&is_active=true&sort=-id')
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'description', 'is_active', 'created_at', 'updated_at']], 'links', 'meta']);

    $res = $this->postJson('/api/tags', [
        'name' => '限時優惠',
        'description' => 'desc',
    ])->assertCreated();

    $id = data_get($res->json(), 'data.id');

    $this->getJson("/api/tags/{$id}")->assertSuccessful()->assertJsonPath('data.id', $id);

    $this->patchJson("/api/tags/{$id}", ['name' => '改名'])->assertSuccessful()->assertJsonPath('data.name', '改名');

    $this->deleteJson("/api/tags/{$id}")->assertNoContent();
});

it('validates tag store request', function () {
    $this->postJson('/api/tags', [])->assertUnprocessable()->assertJsonValidationErrors(['name']);
});

it('manages product-tag relations: list/sync/attach/detach and list products by tag', function () {
    $product = Product::factory()->create(['name' => 'iPhone']);
    $t1 = Tag::factory()->create(['name' => 'hot']);
    $t2 = Tag::factory()->create(['name' => 'new']);

    // attach
    $this->postJson("/api/products/{$product->id}/tags/{$t1->id}")->assertNoContent();
    $this->postJson("/api/products/{$product->id}/tags/{$t1->id}")->assertNoContent(); // idempotent

    // list product tags
    $this->getJson("/api/products/{$product->id}/tags")
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'description', 'is_active', 'created_at', 'updated_at']], 'links', 'meta']);

    // sync
    $this->putJson("/api/products/{$product->id}/tags", ['tag_ids' => [$t2->id]])
        ->assertSuccessful();

    // detach (idempotent)
    $this->deleteJson("/api/products/{$product->id}/tags/{$t2->id}")->assertNoContent();
    $this->deleteJson("/api/products/{$product->id}/tags/{$t2->id}")->assertNoContent();

    // tag products
    $this->getJson("/api/tags/{$t2->id}/products")
        ->assertSuccessful()
        ->assertJsonStructure(['data' => [['id', 'name', 'description', 'price', 'stock', 'is_active', 'created_at', 'updated_at']], 'links', 'meta']);
});
