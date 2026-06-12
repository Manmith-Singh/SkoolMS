<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'class_id', 'subject_id', 'date', 'max_marks', 'pass_marks', 'description',
    ];

    protected $casts = [
        'date' => 'date',
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
    ];

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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
