<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'currency',
        'billing_period', 'max_students', 'max_teachers', 'max_storage_mb',
        'features', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'features'     => 'array',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }
}
