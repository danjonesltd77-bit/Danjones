<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can fetch notifications', function () {
    $user = User::factory()->create();

    // Trigger two notifications
    send_notification($user, 'Test Title 1', 'Test Message 1', 'test_type_1', ['key' => 'value1']);
    send_notification($user, 'Test Title 2', 'Test Message 2', 'test_type_2', ['key' => 'value2']);

    $response = $this->actingAs($user)
        ->getJson('/api/notifications');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'notifications' => [
            '*' => [
                'id',
                'type',
                'title',
                'message',
                'metadata',
                'read_at',
                'created_at',
            ],
        ],
    ]);

    // Here we verify count is 2 and fields are mapped correctly
    $data = $response->json('notifications');
    expect($data)->toHaveCount(2);

    $titles = collect($data)->pluck('title');
    expect($titles)->toContain('Test Title 1');
    expect($titles)->toContain('Test Title 2');
});

test('user can mark notification as read', function () {
    $user = User::factory()->create();

    send_notification($user, 'Test Title', 'Test Message', 'test_type');

    $notification = $user->unreadNotifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->read_at)->toBeNull();

    $response = $this->actingAs($user)
        ->postJson("/api/notifications/{$notification->id}/read");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Notification marked as read successfully.',
        ]);

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();

    send_notification($user, 'Test Title 1', 'Test Message 1', 'test_type');
    send_notification($user, 'Test Title 2', 'Test Message 2', 'test_type');

    expect($user->unreadNotifications()->count())->toEqual(2);

    $response = $this->actingAs($user)
        ->postJson('/api/notifications/read-all');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'All notifications marked as read successfully.',
        ]);

    expect($user->unreadNotifications()->count())->toEqual(0);
});
