<?php

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\TransactionAction;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('unauthenticated users cannot delete their account', function () {
    $response = $this->deleteJson('/api/delete-account', [
        'password' => 'password',
    ]);

    $response->assertStatus(401);
});

test('authenticated users must provide a password to delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->deleteJson('/api/delete-account', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('authenticated users cannot delete their account with an incorrect password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct_password'),
    ]);

    $response = $this->actingAs($user)
        ->deleteJson('/api/delete-account', [
            'password' => 'wrong_password',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('authenticated users can successfully delete their account with correct password', function () {
    Storage::fake('public');

    // Create user with avatar, wallets, transactions, notifications, tokens
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'avatar' => null,
    ]);

    // Create local avatar
    $avatarFile = UploadedFile::fake()->image('avatar.jpg');
    $path = $avatarFile->store('avatars', 'public');
    $user->avatar = Storage::disk('public')->url($path);
    $user->save();

    // Verify avatar exists
    Storage::disk('public')->assertExists('avatars/'.$avatarFile->hashName());

    // Create currency and wallet for the user
    $currency = Currency::factory()->create();
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $currency->id,
    ]);

    // Create a transaction
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'wallet_type' => Wallet::class,
        'currency_id' => $currency->id,
        'action' => TransactionAction::DEPOSIT,
        'amount' => 10.0,
        'usd' => 10.0,
        'type' => TransactionType::CREDIT,
        'previous_balance' => 0.0,
        'current_balance' => 10.0,
        'reference' => 'ref123',
        'status' => TransactionStatus::COMPLETED,
    ]);

    // Create notification
    $user->notify(new class extends \Illuminate\Notifications\Notification
    {
        public function via($notifiable)
        {
            return ['database'];
        }

        public function toArray($notifiable)
        {
            return ['msg' => 'test'];
        }
    });

    // Verify notifications, wallets, transactions, tokens exist
    expect($user->notifications()->count())->toBe(1);
    expect($user->wallets()->count())->toBe(1);
    expect(Transaction::where('user_id', $user->id)->count())->toBe(1);

    // Act as user and delete account
    $response = $this->actingAs($user)
        ->deleteJson('/api/delete-account', [
            'password' => 'secret123',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Your account has been successfully deleted.',
        ]);

    // Assert user is soft deleted
    $this->assertSoftDeleted($user);

    // Assert wallets are deleted
    $this->assertDatabaseMissing('wallets', ['user_id' => $user->id]);

    // Assert transactions are deleted
    $this->assertDatabaseMissing('transactions', ['user_id' => $user->id]);

    // Assert avatar is deleted
    Storage::disk('public')->assertMissing('avatars/'.$avatarFile->hashName());

    // Assert notifications are deleted
    $this->assertDatabaseMissing('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
    ]);
});
