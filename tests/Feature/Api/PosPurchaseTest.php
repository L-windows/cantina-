<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Student;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Product;

class PosPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_successful_and_returns_transaction_and_wallet()
    {
        $operator = User::factory()->create(['role' => 'operator']);
        Sanctum::actingAs($operator);

        $studentUser = User::factory()->create();
        $student = Student::create(['user_id' => $studentUser->id, 'name' => 'Buyer', 'qr_code' => 'QR1']);
        $wallet = Wallet::create(['student_id' => $student->id, 'balance' => 50]);

        $p1 = Product::factory()->create(['price' => 10]);
        $p2 = Product::factory()->create(['price' => 5]);

        $res = $this->postJson('/api/v1/pos/purchase', [
            'qr_code' => 'QR1',
            'product_ids' => [$p1->id, $p2->id]
        ]);

        $res->assertStatus(201)->assertJsonPath('wallet.balance', 35);
        $this->assertDatabaseHas('transactions', ['wallet_id' => $wallet->id, 'type' => 'purchase']);
    }

    public function test_purchase_blocked_by_parental_control()
    {
        $operator = User::factory()->create(['role' => 'operator']);
        Sanctum::actingAs($operator);

        $studentUser = User::factory()->create();
        $student = Student::create(['user_id' => $studentUser->id, 'name' => 'Blocked', 'qr_code' => 'QR2']);
        $wallet = Wallet::create(['student_id' => $student->id, 'balance' => 50]);

        // set parental control record
        $student->parentalControl()->create(['is_blocked' => true]);

        $p = Product::factory()->create(['price' => 10]);

        $this->postJson('/api/v1/pos/purchase', [
            'qr_code' => 'QR2',
            'product_ids' => [$p->id]
        ])->assertStatus(403);
    }
}
