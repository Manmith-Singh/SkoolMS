<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'employee_id', 'first_name', 'last_name', 'email', 'phone',
        'qualification', 'hire_date', 'gender', 'address', 'salary',
        'subject_id', 'class_teacher_id', 'status',
        'pf_number', 'esi_number', 'uan_number', 'bank_account', 'ifsc_code',
        'basic_pay', 'hra', 'da', 'conveyance', 'medical_allowance', 'other_allowances',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'basic_pay' => 'decimal:2',
        'hra' => 'decimal:2',
        'da' => 'decimal:2',
        'conveyance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
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

    public function staffAttendance(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'teacher_id');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function increments(): HasMany
    {
        return $this->hasMany(TeacherIncrement::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
