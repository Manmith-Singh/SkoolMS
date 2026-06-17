<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'year_label', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'academic_year_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'academic_year_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'academic_year_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'academic_year_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'academic_year_id');
    }

    public function feeCategories(): HasMany
    {
        return $this->hasMany(FeeCategory::class, 'academic_year_id');
    }

    public function examTypes(): HasMany
    {
        return $this->hasMany(ExamType::class, 'academic_year_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'academic_year_id');
    }
}
