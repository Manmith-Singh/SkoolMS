<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherIncrement extends Model
{
    protected $connection = 'tenant';
    protected $table = 'teacher_increments';

    protected $fillable = [
        'teacher_id', 'amount', 'effective_date', 'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
