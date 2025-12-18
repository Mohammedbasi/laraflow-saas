<?php

use App\Actions\Auth\EnsureRolesAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);



it('super_admin can list users and filter by tenant', function () {
    $tenant = Tenant::factory()->create();
    User::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $admin = makeSuperAdminUser();

    apiAs($admin)
        ->getJson("/api/v1/admin/users?tenant_id={$tenant->id}")
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('super_admin can set tenant roles for a user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $admin = makeSuperAdminUser();

    apiAs($admin)
        ->putJson("/api/v1/admin/users/{$user->id}/roles", ['roles' => ['tenant_admin']])
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'tenant_admin');
});

it('super_admin can deactivate a user and revoke tokens', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    // give token to ensure revoke works
    $user->createToken('test')->plainTextToken;

    $admin = makeSuperAdminUser();

    apiAs($admin)
        ->postJson("/api/v1/admin/users/{$user->id}/deactivate")
        ->assertOk();

    $user->refresh();
    expect((bool)$user->is_active)->toBeFalse();
    expect($user->tokens()->count())->toBe(0);
});

it('non-super-admin cannot access admin user endpoints', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    apiAs($user)
        ->getJson('/api/v1/admin/users')
        ->assertForbidden();
});
