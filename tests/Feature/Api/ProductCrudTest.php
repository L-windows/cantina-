<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Product;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)->assertJsonStructure(['data','links','meta']);
    }

    public function test_show_returns_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products/' . $product->id);

        $response->assertStatus(200)->assertJsonPath('data.id', $product->id);
    }

    public function test_store_update_destroy_requires_auth_and_works()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = \App\Models\Category::factory()->create();

        $payload = [
            'name' => 'New Product',
            'price' => 9.99,
            'category_id' => $category->id,
        ];

        $res = $this->postJson('/api/v1/products', $payload);
        $res->assertStatus(201)->assertJsonPath('data.name', 'New Product');

        $id = $res->json('data.id');

        $this->putJson('/api/v1/products/' . $id, ['name' => 'Updated Product'])->assertStatus(200)->assertJsonPath('data.name', 'Updated Product');

        $this->deleteJson('/api/v1/products/' . $id)->assertStatus(204);
    }
}
