<?php

use App\Models\Activity;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('super_admin can list activities with filters', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $u1 = User::factory()->create(['tenant_id' => $tenantA->id]);
    $u2 = User::factory()->create(['tenant_id' => $tenantB->id]);

    $super = makeSuperAdminUser(); // this is a real user row

    Activity::withoutGlobalScopes()->create([
        'tenant_id' => $tenantA->id,
        'causer_id' => $u1->id,
        'impersonated_by_user_id' => $super->id, // ✅ valid FK
        'subject_type' => Project::class,
        'subject_id' => 1,
        'description' => 'Test A',
        'meta' => ['k' => 'v'],
    ]);

    Activity::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'causer_id' => $u2->id,
        'subject_type' => 'App\Models\Task',
        'subject_id' => 2,
        'description' => 'Test B',
    ]);

    $token = $super->createToken('admin')->plainTextToken;

    $this->withToken($token)
        ->getJson("/api/v1/admin/activities?tenant_id={$tenantA->id}&subject_type=".Project::class."&impersonated_by_user_id={$super->id}")
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);
});
