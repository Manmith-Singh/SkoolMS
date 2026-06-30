<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('month', 7);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('basic_pay', 10, 2)->default(0);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('da', 10, 2)->default(0);
            $table->decimal('conveyance', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('other_allowances', 10, 2)->default(0);
            $table->decimal('pf_deduction', 10, 2)->default(0);
            $table->decimal('esi_deduction', 10, 2)->default(0);
            $table->decimal('professional_tax', 10, 2)->default(0);
            $table->decimal('income_tax', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('payrolls');
    }
};
