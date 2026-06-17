<?php

use App\Domains\Bank\Models\Bank;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('it can verify a bank account using flutterwave', function () {
    $bank = Bank::create([
        'name' => 'Test Bank',
        'code' => '011',
        'is_active' => true,
    ]);

    Http::fake([
        'api.flutterwave.com/v3/accounts/resolve*' => Http::response([
            'status' => 'success',
            'message' => 'Account number resolved',
            'data' => [
                'account_number' => '0123456789',
                'account_name' => 'TEST ACCOUNT NAME',
            ],
        ], 200),
    ]);

    $response = $this->postJson('/api/bank-accounts/verify', [
        'bank_id' => $bank->id,
        'account_number' => '0123456789',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'account_name' => 'TEST ACCOUNT NAME',
            'account_number' => '0123456789',
            'bank_id' => $bank->id,
        ]);
});

test('it returns error when flutterwave resolution fails', function () {
    $bank = Bank::create([
        'name' => 'Test Bank',
        'code' => '011',
        'is_active' => true,
    ]);

    Http::fake([
        'api.flutterwave.com/v3/accounts/resolve*' => Http::response([
            'status' => 'error',
            'message' => 'Could not resolve account name via Flutterwave.',
        ], 400),
    ]);

    $response = $this->postJson('/api/bank-accounts/verify', [
        'bank_id' => $bank->id,
        'account_number' => '0000000000',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Could not resolve account name via Flutterwave.',
        ]);
});

test('it returns error when bank code is missing', function () {
    $bank = Bank::create([
        'name' => 'Test Bank Without Code',
        'code' => null,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/bank-accounts/verify', [
        'bank_id' => $bank->id,
        'account_number' => '0123456789',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Bank code not found for the selected bank.',
        ]);
});
