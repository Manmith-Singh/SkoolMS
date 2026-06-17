<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'admission_no', 'first_name', 'last_name', 'roll_no',
        'dob', 'gender', 'email', 'phone', 'address',
        'guardian_name', 'guardian_phone', 'admission_date',
        'class_id', 'photo_path',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true));
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
