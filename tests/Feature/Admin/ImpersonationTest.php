<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

it('super_admin can start impersonation and use token to access tenant APIs', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);

    $admin = makeSuperAdminUser();

    // Create a real token for super admin (Bearer)
    $adminToken = $admin->createToken('postman-admin')->plainTextToken;

    // Start impersonation using Bearer token (NOT actingAs)
    $res = $this->withHeader('Authorization', 'Bearer '.$adminToken)
        ->postJson('/api/v1/admin/impersonations/start', [
            'user_id' => $user->id,
            'ttl_minutes' => 60,
        ])
        ->assertCreated();

    $impersonationToken = $res->json('token');
    [$tokenId] = explode('|', $impersonationToken);
    $tokenRow = PersonalAccessToken::findOrFail($tokenId);

    expect($tokenRow->tokenable_id)->toBe($user->id);
    expect($tokenRow->impersonated_by_user_id)->toBe($admin->id);

    $this->app['auth']->forgetGuards();

    // Use impersonation token to call /me
    $this->withToken($impersonationToken)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    $this->app['auth']->forgetGuards();
    // Stop impersonation using impersonation token
    $this->withToken($impersonationToken)
        ->postJson('/api/v1/impersonations/stop')
        ->assertOk();

    $this->app['auth']->forgetGuards();

    // Token should now be invalid
    $this->withToken($impersonationToken)
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});

it('cannot impersonate a super admin', function () {
    $admin1 = makeSuperAdminUser();
    $admin2 = makeSuperAdminUser();

    apiAs($admin1)
        ->postJson('/api/v1/admin/impersonations/start', ['user_id' => $admin2->id])
        ->assertStatus(422);
});
