<?php

use App\Actions\Auth\EnsureRolesAction;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

it('logs impersonation start and project creation with impersonated_by', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_active' => true,
    ]);

    // Ensure tenant roles exist and assign tenant_admin to target user
    app(EnsureRolesAction::class)->ensureTenant($tenant->id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $user->assignRole('tenant_admin');

    $admin = makeSuperAdminUser();
    $adminToken = $admin->createToken('admin')->plainTextToken;

    // start impersonation
    $res = $this->withToken($adminToken)->postJson('/api/v1/admin/impersonations/start', [
        'user_id' => $user->id,
    ])->assertCreated();

    $impToken = $res->json('token');

    // create project as impersonated user
    $this->app['auth']->forgetGuards();

    $this->withToken($impToken)->postJson('/api/v1/projects', [
        'name' => 'Impersonated Project',
        'description' => 'audit',
        'status' => 'pending',
    ])->assertStatus(201);

    // confirm activity exists
    $activities = Activity::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get();

    expect($activities->contains(fn ($a) => $a->description === 'Impersonation started'))->toBeTrue();
    expect($activities->contains(fn ($a) => $a->description === 'Project created'))->toBeTrue();

    $projectLog = $activities->firstWhere('description', 'Project created');
    expect($projectLog->impersonated_by_user_id)->toBe($admin->id);
    expect($projectLog->causer_id)->toBe($user->id);
});
