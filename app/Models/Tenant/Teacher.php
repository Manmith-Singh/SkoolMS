<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'employee_id', 'first_name', 'last_name', 'email', 'phone',
        'qualification', 'hire_date', 'gender', 'address', 'salary',
        'subject_id', 'class_teacher_id', 'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
            ->withTimestamps();
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_teacher_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
