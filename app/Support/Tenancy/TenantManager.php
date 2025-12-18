<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

class TenantManager
{
    private ?int $tenantId = null;

    private bool $superAdmin = false;

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenantId = $tenant?->id;
    }

    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function tenantId(): ?int
    {
        return $this->tenantId;
    }

    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }

    public function setSuperAdmin(bool $value): void
    {
        $this->superAdmin = $value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }
}
