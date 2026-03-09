<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Wallet;
use App\Models\Student;
use App\Models\User;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_own_wallet()
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'name' => 'S']);
        $wallet = Wallet::create(['student_id' => $student->id, 'balance' => 50]);

        Sanctum::actingAs($studentUser);

        $res = $this->getJson('/api/v1/wallet');
        $res->assertStatus(200)->assertJsonPath('data.id', $wallet->id);
    }

    public function test_admin_can_list_wallets()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $s1 = User::factory()->create();
        $st1 = Student::create(['user_id' => $s1->id, 'name' => 'A']);
        Wallet::create(['student_id' => $st1->id, 'balance' => 10]);

        Sanctum::actingAs($admin);

        $res = $this->getJson('/api/v1/wallet');
        $res->assertStatus(200)->assertJsonStructure(['data','links','meta']);
    }
}
