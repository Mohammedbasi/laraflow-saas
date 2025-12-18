<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tenant_admin can view tenant activities, member cannot', function () {
    $tenant = Tenant::factory()->create();

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = User::factory()->create(['tenant_id' => $tenant->id]);

    app(\App\Actions\Auth\EnsureRolesAction::class)->ensureTenant($tenant->id);
    app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

    $admin->assignRole('tenant_admin');
    $member->assignRole('member');

    \App\Models\Activity::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'causer_id' => $admin->id,
        'description' => 'Project created',
    ]);

    // ✅ tenant_admin ok
    $tokenAdmin = $admin->createToken('t')->plainTextToken;

    $this->app['auth']->forgetGuards();
    $this->withToken($tokenAdmin)
        ->getJson('/api/v1/activities')
        ->assertOk();

    // ✅ member forbidden
    $tokenMember = $member->createToken('t')->plainTextToken;

    $this->app['auth']->forgetGuards();
    $this->withToken($tokenMember)
        ->getJson('/api/v1/activities')
        ->assertForbidden();
});
