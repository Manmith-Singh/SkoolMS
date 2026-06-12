<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'exam_id', 'student_id', 'marks_obtained', 'grade', 'remarks',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function percentage(): float
    {
        if (! $this->exam || $this->exam->max_marks == 0) {
            return 0;
        }

        return round(($this->marks_obtained / $this->exam->max_marks) * 100, 2);
    }

    public function isPass(): bool
    {
        return $this->marks_obtained >= ($this->exam->pass_marks ?? 0);
    }
}
