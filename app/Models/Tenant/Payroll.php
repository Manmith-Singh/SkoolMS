<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'teacher_id', 'month',
        'gross_salary', 'basic_pay', 'hra', 'da', 'conveyance',
        'medical_allowance', 'other_allowances',
        'pf_deduction', 'esi_deduction', 'professional_tax',
        'income_tax', 'other_deductions', 'total_deductions',
        'net_salary', 'payment_date', 'status', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'gross_salary' => 'decimal:2',
        'basic_pay' => 'decimal:2',
        'hra' => 'decimal:2',
        'da' => 'decimal:2',
        'conveyance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'esi_deduction' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
