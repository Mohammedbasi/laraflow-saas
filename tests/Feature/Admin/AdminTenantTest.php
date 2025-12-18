<?php

use App\Actions\Auth\EnsureRolesAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);



it('allows super_admin to list tenants', function () {
    Tenant::factory()->count(3)->create();

    $admin = makeSuperAdminUser();

    apiAs($admin)
        ->getJson('/api/v1/admin/tenants')
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('forbids non-super-admin from accessing admin tenants', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    apiAs($user)
        ->getJson('/api/v1/admin/tenants')
        ->assertForbidden();
});
