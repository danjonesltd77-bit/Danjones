<?php

use App\Domains\User\Notifications\ForgotPasswordOtpNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('a user can request a password reset OTP', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'testuser@example.com',
    ]);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'testuser@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Password reset OTP has been sent to your email.',
        ]);

    $user->refresh();
    $this->assertNotNull($user->otp);
    $this->assertNotNull($user->otp_expires_at);

    Notification::assertSentTo($user, ForgotPasswordOtpNotification::class, function ($notification) use ($user) {
        return $notification->otp === $user->otp;
    });
});

test('forgot password fails for non-existent email', function () {
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('a user can reset their password with a valid OTP', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'otp' => '123456',
        'otp_expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/reset-password', [
        'email' => 'testuser@example.com',
        'otp' => '123456',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);

    $user->refresh();
    $this->assertNull($user->otp);
    $this->assertNull($user->otp_expires_at);

    // Verify login with new password works
    $loginResponse = $this->postJson('/api/login', [
        'email' => 'testuser@example.com',
        'password' => 'newpassword123',
    ]);

    $loginResponse->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('reset password fails with invalid OTP', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'otp' => '123456',
        'otp_expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/reset-password', [
        'email' => 'testuser@example.com',
        'otp' => '999999', // Incorrect
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

test('reset password fails with expired OTP', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'otp' => '123456',
        'otp_expires_at' => now()->subMinutes(1), // Expired
    ]);

    $response = $this->postJson('/api/reset-password', [
        'email' => 'testuser@example.com',
        'otp' => '123456',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});
