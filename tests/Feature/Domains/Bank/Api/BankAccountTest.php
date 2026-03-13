<?php

namespace Tests\Feature\Domains\Bank\Api;

use App\Domains\Bank\Models\Bank;
use App\Domains\Bank\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->bank = Bank::factory()->create(['name' => 'Test Bank']);
});

it('can list user bank accounts', function () {
    BankAccount::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'bank_id' => $this->bank->id,
    ]);

    $response = actingAs($this->user)
        ->getJson('/api/bank-accounts')
        ->assertStatus(200)
        ->assertJsonCount(3, 'bank_accounts');
});

it('can store a new bank account', function () {
    $payload = [
        'bank_id' => $this->bank->id,
        'account_name' => 'John Doe',
        'account_number' => '1234567890',
    ];

    $response = actingAs($this->user)
        ->postJson('/api/bank-accounts/store', $payload)
        ->assertStatus(201)
        ->assertJsonPath('data.bank.name', 'Test Bank');

    $this->assertDatabaseHas('bank_accounts', [
        'user_id' => $this->user->id,
        'bank_id' => $this->bank->id,
        'account_number' => '1234567890',
    ]);
});

it('can delete a bank account', function () {
    $bankAccount = BankAccount::factory()->create([
        'user_id' => $this->user->id,
        'bank_id' => $this->bank->id,
    ]);

    actingAs($this->user)
        ->getJson('/api/bank-accounts/delete/'.$bankAccount->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('bank_accounts', [
        'id' => $bankAccount->id,
    ]);
});

it('prevents deleting another users bank account', function () {
    $otherUser = User::factory()->create();
    $bankAccount = BankAccount::factory()->create([
        'user_id' => $otherUser->id,
        'bank_id' => $this->bank->id,
    ]);

    actingAs($this->user)
        ->getJson('/api/bank-accounts/delete/'.$bankAccount->id)
        ->assertStatus(403);
});
