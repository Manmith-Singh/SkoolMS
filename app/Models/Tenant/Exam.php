<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\AcademicYearScope;
use App\Models\Tenant\Traits\HasAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasAcademicYear;
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'class_id', 'exam_type_id', 'from_date', 'to_date',
        'max_marks', 'pass_marks', 'description', 'academic_year_id',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new AcademicYearScope);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * All classes this exam applies to.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_class', 'exam_id', 'class_id')
            ->withTimestamps();
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * All subjects covered in this exam, with per-subject date/notes/order.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'exam_subject', 'exam_id', 'subject_id')
            ->withPivot(['date', 'notes', 'order'])
            ->withTimestamps();
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
