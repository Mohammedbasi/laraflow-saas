<?php

use App\Actions\Tenant\EnsureTenantRolesAction;
use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

// function ensureRolesForTenant(int $tenantId): void
// {
//     app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
//     app(EnsureTenantRolesAction::class)->execute($tenantId);
// }

it('allows tenant_admin to create an invitation but forbids member', function () {
    Mail::fake();

    $tenant = Tenant::factory()->create();
    ensureTenantRoles($tenant->id);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $admin->assignRole('tenant_admin');

    apiAs($admin)
        ->postJson('/api/v1/invitations', ['email' => 'new@user.com'])
        ->assertCreated();

    $member = User::factory()->create(['tenant_id' => $tenant->id]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $member->assignRole('member');

    apiAs($member)
        ->postJson('/api/v1/invitations', ['email' => 'x@user.com'])
        ->assertForbidden();
});

it('accepts an invitation and creates a member user', function () {
    $tenant = Tenant::factory()->create();
    ensureTenantRoles($tenant->id);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $admin->assignRole('tenant_admin');

    // Create invitation record manually (like action does)
    $plainToken = str_repeat('a', 64);
    $inv = Invitation::create([
        'tenant_id' => $tenant->id,
        'invited_by' => $admin->id,
        'email' => 'invitee@acme.com',
        'role' => 'member',
        'token' => hash('sha256', $plainToken),
        'expires_at' => now()->addDays(7),
    ]);

    // Signed accept link works
    $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'invitations.accept',
        $inv->expires_at,
        ['invitation' => $inv->id, 'token' => $plainToken]
    );

    $this->getJson($signedUrl)
        ->assertOk()
        ->assertJsonFragment(['email' => 'invitee@acme.com']);

    // Complete invitation (creates user + token)
    $res = $this->postJson('/api/v1/invitations/complete', [
        'invitation_id' => $inv->id,
        'token' => $plainToken,
        'name' => 'Invitee',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test',
    ]);

    $res->assertCreated()->assertJsonStructure(['token', 'user' => ['id', 'tenant_id', 'name', 'email']]);

    $user = User::where('email', 'invitee@acme.com')->first();
    expect($user)->not->toBeNull();
    expect($user->tenant_id)->toBe($tenant->id);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    expect($user->hasRole('member'))->toBeTrue();

    $inv->refresh();
    expect($inv->accepted_at)->not->toBeNull();
});
