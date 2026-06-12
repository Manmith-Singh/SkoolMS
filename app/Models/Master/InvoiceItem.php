<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $connection = 'mysql';

    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit_price', 'amount'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
