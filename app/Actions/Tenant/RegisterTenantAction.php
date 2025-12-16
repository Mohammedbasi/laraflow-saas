<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            ]);

            // 3) Update tenant owner_id
            $tenant->update(['owner_id' => $user->id]);

            // 4) Create token
            $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

            return compact('tenant', 'user', 'token');
        });
    }
}
