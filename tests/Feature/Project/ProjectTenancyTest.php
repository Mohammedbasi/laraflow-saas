<?php

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('lists only projects for the authenticated user tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

    $projectsA = Project::factory()->count(2)->create(['tenant_id' => $tenantA->id]);
    Project::factory()->count(3)->create(['tenant_id' => $tenantB->id]);

    apiAs($userA)
        ->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $projectsA[0]->id])
        ->assertJsonFragment(['id' => $projectsA[1]->id]);
});

it('cannot show a project that belongs to another tenant (404)', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $projectB = Project::factory()->create(['tenant_id' => $tenantB->id]);

    apiAs($userA)
        ->getJson("/api/v1/projects/{$projectB->id}")
        ->assertNotFound();
});

it('creates a project and automatically sets tenant_id to the current tenant', function () {
    $tenantA = Tenant::factory()->create();
    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

    $payload = [
        'name' => 'Tenant A Project',
        'description' => 'Desc',
        'status' => 'pending',
    ];

    $response = apiAs($userA)->postJson('/api/v1/projects', $payload);

    $response->assertCreated();

    $projectId = $response->json('data.id');

    expect(Project::find($projectId)->tenant_id)->toBe($tenantA->id);
});

it('cannot update another tenant project (404)', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = UserFactory::new()->create(['tenant_id' => $tenantA->id]);
    $projectB = Project::factory()->create(['tenant_id' => $tenantB->id]);

    apiAs($userA)
        ->putJson("/api/v1/projects/{$projectB->id}", ['name' => 'Hacked'])
        ->assertNotFound();
});

it('cannot delete another tenant project (404)', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $projectB = Project::factory()->create(['tenant_id' => $tenantB->id]);

    apiAs($userA)
        ->deleteJson("/api/v1/projects/{$projectB->id}")
        ->assertNotFound();
});
