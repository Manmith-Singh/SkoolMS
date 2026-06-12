<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'plan_id', 'starts_at', 'ends_at', 'status', 'cancelled_at',
    ];

    protected $casts = [
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'cancelled_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && (! $this->ends_at || $this->ends_at->isFuture());
    }
}
