<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\AcademicYearScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $connection = 'tenant';

    protected $table = 'attendance';

    protected $fillable = [
        'student_id', 'class_id', 'date', 'status', 'remarks', 'marked_by',
        'academic_year_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new AcademicYearScope);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
