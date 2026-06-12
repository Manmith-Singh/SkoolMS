<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'description', 'default_amount', 'frequency', 'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'category_id');
    }
}
