<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use Billable, HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'owner_id',
        'plan_type',
        'is_suspended',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isFreePlan(): bool
    {
        return ($this->plan_type ?? 'free') === 'free';
    }

    /**
     * “Paid effective” means:
     * - subscription active OR
     * - subscription canceled but still within grace period (ends_at in future)
     */
    public function isPaidEffective(string $subscriptionName = 'default'): bool
    {
        $sub = $this->subscription($subscriptionName);

        if (! $sub) {
            return false;
        }

        return $sub->active() || $sub->onGracePeriod();
    }
}
