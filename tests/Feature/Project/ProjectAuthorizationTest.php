<?php

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

it('allows tenant_admin to create a project', function () {
    $tenant = Tenant::factory()->create();

    ensureTenantRoles($tenant->id);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    app(TenantManager::class)->setTenantId($admin->tenant_id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($admin->tenant_id);
    $admin->assignRole('tenant_admin');

    apiAs($admin)
        ->postJson('/api/v1/projects', [
            'name' => 'Admin Project',
            'status' => 'pending',
        ])
        ->assertCreated();
});

it('prevents member from creating a project (403)', function () {
    $tenant = Tenant::factory()->create();

    ensureTenantRoles($tenant->id);

    $member = User::factory()->create(['tenant_id' => $tenant->id]);
    app(TenantManager::class)->setTenantId($member->tenant_id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($member->tenant_id);
    $member->assignRole('member');

    apiAs($member)
        ->postJson('/api/v1/projects', [
            'name' => 'Member Project',
            'status' => 'pending',
        ])
        ->assertForbidden();
});

it('allows member to list projects', function () {
    $tenant = Tenant::factory()->create();

    ensureTenantRoles($tenant->id);

    $member = User::factory()->create(['tenant_id' => $tenant->id]);
    app(TenantManager::class)->setTenantId($member->tenant_id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($member->tenant_id);
    $member->assignRole('member');

    Project::factory()->count(2)->create(['tenant_id' => $tenant->id]);

    apiAs($member)
        ->getJson('/api/v1/projects')
        ->assertOk();
});

it('prevents member from updating a project (403)', function () {
    $tenant = Tenant::factory()->create();
    ensureTenantRoles($tenant->id);

    $project = Project::factory()->create(['tenant_id' => $tenant->id]);

    $member = User::factory()->create(['tenant_id' => $tenant->id]);
    app(TenantManager::class)->setTenantId($member->tenant_id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($member->tenant_id);
    $member->assignRole('member');

    apiAs($member)
        ->putJson("/api/v1/projects/{$project->id}", ['name' => 'Nope'])
        ->assertForbidden();
});

it('prevents member from deleting a project (403)', function () {
    $tenant = Tenant::factory()->create();
    ensureTenantRoles($tenant->id);

    $project = Project::factory()->create(['tenant_id' => $tenant->id]);

    $member = User::factory()->create(['tenant_id' => $tenant->id]);
    app(TenantManager::class)->setTenantId($member->tenant_id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($member->tenant_id);
    $member->assignRole('member');

    apiAs($member)
        ->deleteJson("/api/v1/projects/{$project->id}")
        ->assertForbidden();
});
