<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\AcademicYearScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenditureTransaction extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'expenditure_type_id', 'amount', 'date', 'description',
        'reference', 'paid_by', 'approved_by', 'academic_year_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new AcademicYearScope);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function expenditureType(): BelongsTo
    {
        return $this->belongsTo(ExpenditureType::class, 'expenditure_type_id');
    }
}
