<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\AcademicYearScope;
use App\Models\Tenant\Traits\HasAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    use HasAcademicYear;
    protected $connection = 'tenant';

    protected $table = 'fee_categories';

    protected $fillable = [
        'name', 'description', 'default_amount', 'frequency', 'is_active',
        'academic_year_id',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new AcademicYearScope);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'category_id');
    }
}
