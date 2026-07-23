<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can update own name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/auth/me', [
        'name' => 'New Name',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'status' => true,
            'data' => ['name' => 'New Name'],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});

it('can update password with correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/auth/me', [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertOk()->assertJson(['status' => true]);

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('cannot update password without current password', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/auth/me', [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

it('cannot update password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/auth/me', [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

it('cannot change email through the profile update endpoint', function () {
    $user = User::factory()->create(['email' => 'original@example.com']);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/auth/me', [
        'name' => 'New Name',
        'email' => 'hacked@example.com',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'original@example.com',
    ]);
});

it('requires authentication to update profile', function () {
    $response = $this->patchJson('/api/v1/auth/me', [
        'name' => 'New Name',
    ]);

    $response->assertUnauthorized();
});
