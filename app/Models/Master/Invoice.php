<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'invoice_number', 'tenant_id', 'subscription_id',
        'subtotal', 'tax', 'total', 'currency',
        'status', 'issue_date', 'due_date', 'paid_at', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'paid_at'    => 'datetime',
        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()
            ->where('status', 'succeeded')
            ->sum('amount');
    }

    public function amountDue(): float
    {
        return (float) $this->total - $this->amountPaid();
    }
}
