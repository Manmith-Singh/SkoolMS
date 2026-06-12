<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'fee_id', 'student_id', 'amount_paid', 'payment_date',
        'mode', 'transaction_ref', 'receipt_no', 'notes', 'received_by',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
