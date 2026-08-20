<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated users cannot update FCM token', function () {
    $response = $this->postJson('/api/fcm-token', [
        'fcm_token' => 'fcm_token_123',
    ]);

    $response->assertStatus(401);
});

test('authenticated users must provide fcm_token', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/fcm-token', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['fcm_token']);
});

test('authenticated users can update FCM token', function () {
    $user = User::factory()->create([
        'fcm_token' => null,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/fcm-token', [
            'fcm_token' => 'fcm_token_123',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'FCM token updated successfully.',
        ]);

    $user->refresh();
    $this->assertEquals('fcm_token_123', $user->fcm_token);
});
