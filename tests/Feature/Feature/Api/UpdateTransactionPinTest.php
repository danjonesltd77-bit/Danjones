<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

it('allows an authenticated user to update their transaction pin', function () {
    /** @var User $user */
    $user = User::factory()->create(['pin' => Hash::make('1234')]);

    $response = actingAs($user)->putJson('/api/user/transaction-pin', [
        'current_pin'          => '1234',
        'pin'                  => '5678',
        'pin_confirmation'     => '5678',
    ]);

    $response->assertStatus(200)->assertJson([
        'success' => true,
        'message' => 'Transaction PIN updated successfully.',
    ]);

    $user->refresh();
    expect(Hash::check('5678', $user->pin))->toBeTrue();
});

it('rejects an incorrect current pin', function () {
    /** @var User $user */
    $user = User::factory()->create(['pin' => Hash::make('1234')]);

    $response = actingAs($user)->putJson('/api/user/transaction-pin', [
        'current_pin'      => '0000',
        'pin'              => '5678',
        'pin_confirmation' => '5678',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_pin']);
});

it('prevents an unauthenticated user from updating the pin', function () {
    $response = putJson('/api/user/transaction-pin', [
        'current_pin'      => '1234',
        'pin'              => '5678',
        'pin_confirmation' => '5678',
    ]);

    $response->assertStatus(401);
});

it('validates that the new pin differs from the current pin', function () {
    /** @var User $user */
    $user = User::factory()->create(['pin' => Hash::make('1234')]);

    $response = actingAs($user)->putJson('/api/user/transaction-pin', [
        'current_pin'      => '1234',
        'pin'              => '1234',
        'pin_confirmation' => '1234',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});

it('validates that the new pin matches the confirmation', function () {
    /** @var User $user */
    $user = User::factory()->create(['pin' => Hash::make('1234')]);

    $response = actingAs($user)->putJson('/api/user/transaction-pin', [
        'current_pin'      => '1234',
        'pin'              => '5678',
        'pin_confirmation' => '9999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pin']);
});
