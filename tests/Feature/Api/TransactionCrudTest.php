<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Student;
use App\Models\User;

class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_create_purchase_and_balance_reduced()
    {
        // create a student (with user) and wallet
        $studentUser = User::factory()->create();
        $student = Student::create(['user_id' => $studentUser->id, 'name' => 'Sam Student']);
        $wallet = Wallet::create(['student_id' => $student->id, 'balance' => 100]);

        $operator = User::factory()->create(['role' => 'operator']);
        Sanctum::actingAs($operator);

        $payload = [
            'wallet_id' => $wallet->id,
            'amount' => 10.00,
            'type' => 'purchase',
            'description' => 'Lunch purchase'
        ];

        $res = $this->postJson('/api/v1/transactions', $payload);
        $res->assertStatus(201);
        $this->assertEquals(10.0, (float) $res->json('data.amount'));

        $wallet->refresh();
        $this->assertEquals(90.00, (float) $wallet->balance);
    }

    public function test_insufficient_balance_returns_error()
    {
        $studentUser = User::factory()->create();
        $student = Student::create(['user_id' => $studentUser->id, 'name' => 'Sam Student']);
        $wallet = Wallet::create(['student_id' => $student->id, 'balance' => 5]);

        $operator = User::factory()->create(['role' => 'operator']);
        Sanctum::actingAs($operator);

        $payload = [
            'wallet_id' => $wallet->id,
            'amount' => 10.00,
            'type' => 'purchase',
        ];

        $this->postJson('/api/v1/transactions', $payload)->assertStatus(400);
    }
}
