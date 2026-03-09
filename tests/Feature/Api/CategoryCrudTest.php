<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Category;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)->assertJsonStructure(['data','links','meta']);
    }

    public function test_show_returns_category_with_products()
    {
        $category = Category::factory()->has(\App\Models\Product::factory()->count(2))->create();

        $response = $this->getJson('/api/v1/categories/' . $category->id);

        $response->assertStatus(200)->assertJsonPath('data.id', $category->id);
        $this->assertCount(2, $response->json('data.products'));
    }

    public function test_store_update_destroy_requires_auth_and_works()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = ['name' => 'New Category'];
        $res = $this->postJson('/api/v1/categories', $payload);
        $res->assertStatus(201)->assertJsonPath('data.name', 'New Category');

        $id = $res->json('data.id');

        $this->putJson('/api/v1/categories/' . $id, ['name' => 'Updated'])->assertStatus(200)->assertJsonPath('data.name', 'Updated');

        $this->deleteJson('/api/v1/categories/' . $id)->assertStatus(204);
    }
}
