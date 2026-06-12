<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $connection = 'tenant';

    protected $table = 'classes';

    protected $fillable = [
        'name', 'section', 'capacity', 'description',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->section ? "{$this->name} - {$this->section}" : $this->name;
    }
}
