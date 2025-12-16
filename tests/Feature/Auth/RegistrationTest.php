<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

it('registers a tenant and owner user and assigns tenant_admin role', function () {
    $payload = [
        'company_name' => 'Mohammed Inc',
        'domain' => 'moh',
        'name' => 'Mohammed',
        'email' => 'owner@moh.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'postman',
    ];

    $res = $this->postJson('/api/v1/auth/register', $payload);

    $user = User::where('email', 'owner@moh.com')->first();

     // set tenant context for role checks
    app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

    expect($user->hasRole('tenant_admin'))->toBeTrue();

    $res->assertCreated()
        ->assertJsonStructure([
            'token',
            'tenant' => ['id', 'name', 'domain', 'owner_id', 'plan_type'],
            'user' => ['id', 'tenant_id', 'name', 'email'],
        ]);

    $tenant = Tenant::where('domain', 'moh')->first();
    expect($tenant)->not->toBeNull();

    $user = User::where('email', 'owner@moh.com')->first();
    expect($user->tenant_id)->toBe($tenant->id);
    expect($tenant->owner_id)->toBe($user->id);
});
