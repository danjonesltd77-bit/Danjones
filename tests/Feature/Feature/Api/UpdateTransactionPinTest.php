<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('allows an authenticated user to update their transaction pin', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'pin'      => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/update-transaction-pin', [
        'current_password' => 'secret123',
        'pin'              => '5678',
        'pin_confirmation' => '5678',
    ]);

    $response->assertStatus(200)->assertJson([
        'success' => true,
        'message' => 'Transaction PIN updated successfully.',
    ]);

    $user->refresh();
    expect(Hash::check('5678', $user->pin))->toBeTrue();
});

it('rejects an incorrect account password', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'pin'      => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/update-transaction-pin', [
        'current_password' => 'wrong-password',
        'pin'              => '5678',
        'pin_confirmation' => '5678',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

it('prevents an unauthenticated user from updating the pin', function () {
    $response = postJson('/api/update-transaction-pin', [
        'current_password' => 'secret123',
        'pin'              => '5678',
        'pin_confirmation' => '5678',
    ]);

    $response->assertStatus(401);
});

it('validates that the new pin is exactly 4 digits', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'pin'      => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/update-transaction-pin', [
        'current_password' => 'secret123',
        'pin'              => '12345',
        'pin_confirmation' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});

it('validates that the new pin matches the confirmation', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'pin'      => Hash::make('1234'),
    ]);

    $response = actingAs($user)->postJson('/api/update-transaction-pin', [
        'current_password' => 'secret123',
        'pin'              => '5678',
        'pin_confirmation' => '9999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});
    