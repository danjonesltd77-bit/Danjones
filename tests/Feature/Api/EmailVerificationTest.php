<?php

use App\Domains\User\Notifications\SendOtpNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('user registration generates and sends otp', function () {
    Notification::fake();

    // Mock wallet creation to avoid dependency on HD wallets/Gateways
    $this->mock(\App\Domains\Wallet\Actions\CreateDefaultWalletsAction::class, function ($mock) {
        $mock->shouldReceive('execute')->once();
    });

    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '08012345678',
    ]);

    $response->assertStatus(200);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->otp)->not->toBeNull();
    expect($user->otp_expires_at)->not->toBeNull();

    Notification::assertSentTo($user, SendOtpNotification::class, function ($notification) use ($user) {
        return $notification->otp === $user->otp;
    });
});

test('user can verify email with correct otp', function () {
    $user = User::factory()->unverified()->create([
        'otp' => '123456',
        'otp_expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/verify-otp', [
            'otp' => '123456',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
    expect($user->fresh()->otp)->toBeNull();
});

test('user cannot verify email with incorrect otp', function () {
    $user = User::factory()->unverified()->create([
        'otp' => '123456',
        'otp_expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/verify-otp', [
            'otp' => '654321',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('user cannot verify email with expired otp', function () {
    $user = User::factory()->unverified()->create([
        'otp' => '123456',
        'otp_expires_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/verify-otp', [
            'otp' => '123456',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('user can resend otp', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create([
        'otp' => '111111',
        'otp_expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/resend-otp');

    $response->assertStatus(200);

    $user->refresh();
    expect($user->otp)->not->toBe('111111');

    Notification::assertSentTo($user, SendOtpNotification::class);
});
