<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('allows an authenticated user to set a transaction pin', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/api/transaction-pin', [
        'pin' => '1234',
        'pin_confirmation' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Transaction PIN set successfully.',
        ]);

    $user->refresh();
    expect(Hash::check('1234', $user->pin))->toBeTrue();
});

it('prevents an unauthenticated user from setting a transaction pin', function () {
    $response = postJson('/api/transaction-pin', [
        'pin' => '1234',
        'pin_confirmation' => '1234',
    ]);

    $response->assertStatus(401);
});

it('validates that the pin is exactly 4 digits', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/api/transaction-pin', [
        'pin' => '12345',
        'pin_confirmation' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});

it('validates that the pin matches the confirmation', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/api/transaction-pin', [
        'pin' => '1234',
        'pin_confirmation' => '4321',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});
