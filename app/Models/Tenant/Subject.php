<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'code', 'class_id', 'description',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * All classes this subject is offered in.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'subject_class', 'subject_id', 'class_id')
            ->withTimestamps();
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
