<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fee extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'student_id', 'category_id', 'amount', 'paid_amount',
        'due_date', 'status', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'category_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function balance(): float
    {
        return round(((float) $this->amount) - ((float) $this->paid_amount), 2);
    }

    public function isFullyPaid(): bool
    {
        return $this->balance() <= 0;
    }
}
