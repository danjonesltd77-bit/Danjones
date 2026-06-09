<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('unauthenticated users cannot update their profile', function () {
    $response = $this->postJson('/api/update-profile', [
        'phone' => '1234567890',
    ]);

    $response->assertStatus(401);
});

test('authenticated users must provide at least one field to update profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/update-profile', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone', 'avatar']);
});

test('authenticated users can update their phone number', function () {
    $user = User::factory()->create([
        'phone' => '1234567890',
        'phone_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/update-profile', [
            'phone' => '0987654321',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'phone' => '0987654321',
            ],
        ]);

    $user->refresh();
    $this->assertEquals('0987654321', $user->phone);
    $this->assertNull($user->phone_verified_at);
});

test('updating to the same phone number does not reset verification status', function () {
    $verifiedAt = now()->subDays(1);
    $user = User::factory()->create([
        'phone' => '1234567890',
        'phone_verified_at' => $verifiedAt,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/update-profile', [
            'phone' => '1234567890',
        ]);

    $response->assertStatus(200);

    $user->refresh();
    $this->assertEquals('1234567890', $user->phone);
    $this->assertNotNull($user->phone_verified_at);
});

test('phone number must be unique except for the current user', function () {
    $otherUser = User::factory()->create([
        'phone' => '0987654321',
    ]);

    $user = User::factory()->create([
        'phone' => '1234567890',
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/update-profile', [
            'phone' => '0987654321', // Exists on other user
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('users can upload an avatar/profile picture', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => null,
    ]);

    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)
        ->postJson('/api/update-profile', [
            'avatar' => $avatar,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $user->refresh();
    $this->assertNotNull($user->avatar);

    // Assert the file was stored on the disk
    $path = str_replace(Storage::disk('public')->url(''), '', $user->avatar);
    Storage::disk('public')->assertExists(ltrim($path, '/'));
});

test('uploading a new avatar deletes the old one', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => null,
    ]);

    // First upload
    $avatar1 = UploadedFile::fake()->image('first.jpg');
    $this->actingAs($user)
        ->postJson('/api/update-profile', ['avatar' => $avatar1])
        ->assertStatus(200);

    $user->refresh();
    $firstAvatarUrl = $user->avatar;
    $firstPath = str_replace(Storage::disk('public')->url(''), '', $firstAvatarUrl);
    Storage::disk('public')->assertExists(ltrim($firstPath, '/'));

    // Second upload
    $avatar2 = UploadedFile::fake()->image('second.jpg');
    $this->actingAs($user)
        ->postJson('/api/update-profile', ['avatar' => $avatar2])
        ->assertStatus(200);

    $user->refresh();
    $secondAvatarUrl = $user->avatar;
    $secondPath = str_replace(Storage::disk('public')->url(''), '', $secondAvatarUrl);

    // Assert new exists, old deleted
    Storage::disk('public')->assertExists(ltrim($secondPath, '/'));
    Storage::disk('public')->assertMissing(ltrim($firstPath, '/'));
});
