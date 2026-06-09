<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('allows an authenticated user to verify their correct transaction pin', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'pin' => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/verify-transaction-pin', [
        'pin' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Transaction PIN matches.',
        ]);
});

test('prevents an unauthenticated user from verifying a transaction pin', function () {
    $response = postJson('/api/verify-transaction-pin', [
        'pin' => '1234',
    ]);

    $response->assertStatus(401);
});

test('fails validation if the transaction pin is incorrect', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'pin' => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/verify-transaction-pin', [
        'pin' => '9999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});

test('fails validation if the transaction pin is not 4 digits', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'pin' => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/verify-transaction-pin', [
        'pin' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});
