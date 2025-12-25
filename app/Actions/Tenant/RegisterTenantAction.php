<?php

namespace App\Actions\Tenant;

use App\Actions\Auth\EnsureRolesAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RegisterTenantAction
{
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1) Create tenant (owner_id is null for now)
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'domain' => $data['domain'] ?? null,
                'plan_type' => 'free',
            ]);

            // 2) Create owner user (must set tenant_id explicitly)

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_manager' => true,
            ]);

            // 3) Update tenant owner_id
            $tenant->update(['owner_id' => $user->id]);

            // IMPORTANT: set tenant context for Spatie
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

            // Ensure default tenant roles exist
            app(EnsureRolesAction::class)->ensureTenant($tenant->id);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            // Assign owner role
            $user->assignRole('tenant_admin');

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // 4) Create token
            $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

            return compact('tenant', 'user', 'token');
        });
    }
}
