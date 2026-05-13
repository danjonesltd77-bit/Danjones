<?php

use App\Livewire\Admin\System\LogViewer;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->markEmailAsVerified();
});

it('can access the log viewer page', function () {
    $this->actingAs($this->user)
        ->get(route('admin.system.logs'))
        ->assertStatus(200);
});

it('lists log files', function () {
    File::put(storage_path('logs/test-log.log'), 'test entry');

    Livewire::actingAs($this->user)
        ->test(LogViewer::class)
        ->assertSee('test-log.log');

    File::delete(storage_path('logs/test-log.log'));
});

it('can search logs', function () {
    File::put(storage_path('logs/search-test.log'), "[2024-01-01 00:00:00] local.INFO: TargetMessage\n[2024-01-01 00:00:01] local.INFO: OtherMessage");

    Livewire::actingAs($this->user)
        ->test(LogViewer::class, ['selectedFile' => 'search-test.log'])
        ->set('search', 'TargetMessage')
        ->assertSee('TargetMessage')
        ->assertDontSee('OtherMessage');

    File::delete(storage_path('logs/search-test.log'));
});

it('can filter by level', function () {
    File::put(storage_path('logs/level-test.log'), "[2024-01-01 00:00:00] local.ERROR: ErrorMsg\n[2024-01-01 00:00:01] local.INFO: InfoMsg");

    Livewire::actingAs($this->user)
        ->test(LogViewer::class, ['selectedFile' => 'level-test.log'])
        ->set('level', 'error')
        ->assertSee('ErrorMsg')
        ->assertDontSee('InfoMsg');

    File::delete(storage_path('logs/level-test.log'));
});
