<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class AdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_delete_and_promote_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // create
        $res = $this->postJson('/api/v1/admin/users', [
            'name' => 'New',
            'email' => 'new@example.com',
            'password' => 'secret',
            'role' => 'student'
        ]);
        $res->assertStatus(201)->assertJsonPath('email', 'new@example.com');

        $id = $res->json('id');

        // update
        $this->putJson('/api/v1/admin/users/' . $id, ['name' => 'Updated'])->assertStatus(200)->assertJsonPath('name', 'Updated');

        // promote
        $this->postJson('/api/v1/admin/users/' . $id . '/promote', ['role' => 'operator'])->assertStatus(200)->assertJsonPath('role', 'operator');

        // delete
        $this->deleteJson('/api/v1/admin/users/' . $id)->assertStatus(204);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/users')->assertStatus(403);
    }
}
