<?php

use App\Domains\Core\Models\Setting;
use App\Livewire\Admin\System\SettingsManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Admin user who has the permission
    $this->adminUser = User::factory()->create();
    $this->adminUser->givePermissionTo('manage settings');
    $this->adminUser->markEmailAsVerified();

    // Regular user who lacks the permission
    $this->unauthorizedUser = User::factory()->create();
    $this->unauthorizedUser->markEmailAsVerified();
});

test('unauthenticated users are redirected from the settings page', function () {
    $this->get(route('admin.system.settings'))
        ->assertRedirect('/login');
});

test('unauthorized users cannot access the settings page', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get(route('admin.system.settings'))
        ->assertStatus(403);
});

test('authorized users can access the settings page', function () {
    $this->actingAs($this->adminUser)
        ->get(route('admin.system.settings'))
        ->assertStatus(200);
});

test('authorized users can list existing settings', function () {
    Setting::create([
        'key' => 'cashback_buy',
        'value' => '1.5',
        'type' => 'float',
        'description' => 'Buying cashback rate',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(SettingsManagement::class)
        ->set('activeTab', 'all')
        ->assertSee('cashback_buy')
        ->assertSee('Buying cashback rate');
});

test('authorized users can register a new setting key', function () {
    Livewire::actingAs($this->adminUser)
        ->test(SettingsManagement::class)
        ->set('newKey', 'new_config_key')
        ->set('newValue', '100')
        ->set('newType', 'integer')
        ->set('newDescription', 'New test key description')
        ->call('createSetting')
        ->assertHasNoErrors()
        ->assertDispatched('toast', message: 'New setting key registered successfully!');

    $this->assertDatabaseHas('settings', [
        'key' => 'new_config_key',
        'value' => '100',
        'type' => 'integer',
        'description' => 'New test key description',
    ]);
});

test('authorized users can edit an existing setting key inline with type validation', function () {
    $setting = Setting::create([
        'key' => 'cashback_sell',
        'value' => '0.5',
        'type' => 'float',
        'description' => 'Selling cashback rate',
    ]);

    // Test invalid float validation
    Livewire::actingAs($this->adminUser)
        ->test(SettingsManagement::class)
        ->call('startEdit', $setting->id)
        ->set('editingValue', 'invalid-float-string')
        ->call('saveEdit', $setting->id)
        ->assertDispatched('toast', message: 'Value must be a valid float number.');

    // Value should NOT have changed in DB
    expect($setting->fresh()->value)->toEqual('0.5');

    // Test valid float update
    Livewire::actingAs($this->adminUser)
        ->test(SettingsManagement::class)
        ->call('startEdit', $setting->id)
        ->set('editingValue', '1.25')
        ->call('saveEdit', $setting->id)
        ->assertHasNoErrors()
        ->assertDispatched('toast', message: "Setting 'cashback_sell' updated successfully!");

    // Value should have updated in DB
    expect($setting->fresh()->value)->toEqual('1.25');
});

test('authorized users can delete a setting', function () {
    $setting = Setting::create([
        'key' => 'temp_setting_key',
        'value' => 'some-val',
        'type' => 'string',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(SettingsManagement::class)
        ->call('deleteSetting', $setting->id)
        ->assertDispatched('toast', message: 'Setting deleted successfully!');

    $this->assertDatabaseMissing('settings', [
        'id' => $setting->id,
    ]);
});
