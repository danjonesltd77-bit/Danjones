<?php

use App\Domains\Kyc\Models\UserVerification;
use App\Domains\Kyc\Models\Verification;
use App\Mail\Kyc\NinVerifiedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Verification::create(['name' => 'NIN']);

    config(['services.qoreid.base_url' => 'https://api.qoreid.com']);
    config(['services.qoreid.client_id' => 'test_client_id']);
    config(['services.qoreid.secret' => 'test_secret']);
});

it('can list verification types and user verifications', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->getJson('/api/verifications');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data',
            'user_verifications',
        ])
        ->assertJsonFragment(['name' => 'NIN']);
});

it('can successfully verify NIN with mocked QoreID response', function () {
    Mail::fake();

    /** @var User $user */
    $user = User::factory()->create();

    // Mock token request
    Http::fake([
        'https://api.qoreid.com/token' => Http::response(['accessToken' => 'mock_token'], 200),
        'https://api.qoreid.com/v1/ng/identities/nin/12345678901' => Http::response([
            'status' => ['status' => 'verified'],
            'reference' => 'REF123',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => 'Quincy',
        ], 200),
    ]);

    $response = actingAs($user)->postJson('/api/verifications/verify-nin', [
        'nin' => '12345678901',
        'firstname' => 'John',
        'lastname' => 'Doe',
        'middlename' => 'Quincy',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'NIN verification successful.',
        ]);

    $this->assertDatabaseHas('user_verifications', [
        'user_id' => $user->id,
        'status' => 'approved',
        'reference' => 'REF123',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'John Quincy Doe',
    ]);

    Mail::assertQueued(NinVerifiedMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->status === 'approved';
    });
});

it('handles failed NIN verification from QoreID', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Mock token request
    Http::fake([
        'https://api.qoreid.com/token' => Http::response(['accessToken' => 'mock_token'], 200),
        'https://api.qoreid.com/v1/ng/identities/nin/11111111111' => Http::response([
            'status' => ['status' => 'failed'],
            'message' => 'NIN does not match records',
        ], 422),
    ]);

    $response = actingAs($user)->postJson('/api/verifications/verify-nin', [
        'nin' => '11111111111',
        'firstname' => 'Wrong',
        'lastname' => 'Name',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'NIN does not match records',
        ]);

    $this->assertDatabaseHas('user_verifications', [
        'user_id' => $user->id,
        'status' => 'rejected',
        'reason' => 'NIN does not match records',
    ]);
});

it('validates NIN input format', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/api/verifications/verify-nin', [
        'nin' => '123', // too short
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nin']);
});

it('prevents verification if the user is already verified', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $verificationType = Verification::where('name', 'NIN')->first();

    // Create an approved verification record
    UserVerification::create([
        'user_id' => $user->id,
        'verification_id' => $verificationType->id,
        'status' => 'approved',
        'data' => [],
    ]);

    $response = actingAs($user)->postJson('/api/verifications/verify-nin', [
        'nin' => '12345678901',
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are already verified.',
        ]);
});

it('prevents verification if the user has reached 5 failed attempts', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $verificationType = Verification::where('name', 'NIN')->first();

    // Create 5 rejected verification records
    for ($i = 0; $i < 5; $i++) {
        UserVerification::create([
            'user_id' => $user->id,
            'verification_id' => $verificationType->id,
            'status' => 'rejected',
            'data' => [],
        ]);
    }

    $response = actingAs($user)->postJson('/api/verifications/verify-nin', [
        'nin' => '12345678901',
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You have reached the maximum number of failed verification attempts.',
        ]);
});

it('prevents verification if the NIN is already verified by another user', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create();
    /** @var User $user2 */
    $user2 = User::factory()->create();
    $verificationType = Verification::where('name', 'NIN')->first();

    // Create an approved verification record for user1 with a specific NIN
    UserVerification::create([
        'user_id' => $user1->id,
        'verification_id' => $verificationType->id,
        'status' => 'approved',
        'data' => ['nin' => '12345678901'],
    ]);

    // User2 tries to verify the same NIN
    $response = actingAs($user2)->postJson('/api/verifications/verify-nin', [
        'nin' => '12345678901',
        'firstname' => 'Jane',
        'lastname' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This NIN has already been verified by another user.',
        ]);
});

it('returns kyc verification status on user response', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // 1. Assert default 'unverified' status
    $response = actingAs($user)->getJson('/api/user');
    $response->assertStatus(200)
        ->assertJsonPath('kyc_status', 'unverified')
        ->assertJsonPath('kyc_verified', false);

    // 2. Add approved verification and assert approved status
    $verificationType = Verification::where('name', 'NIN')->first();
    UserVerification::create([
        'user_id' => $user->id,
        'verification_id' => $verificationType->id,
        'status' => 'approved',
        'data' => [],
    ]);

    $response = actingAs($user)->getJson('/api/user');
    $response->assertStatus(200)
        ->assertJsonPath('kyc_status', 'approved')
        ->assertJsonPath('kyc_verified', true);
});
