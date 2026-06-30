<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\AcademicYearScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeType extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'description', 'is_active', 'academic_year_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new AcademicYearScope);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(IncomeTransaction::class, 'income_type_id');
    }
}
