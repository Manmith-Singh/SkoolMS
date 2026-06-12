<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_id')->constrained('fees')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->date('payment_date');
            $table->enum('mode', ['cash', 'cheque', 'bank_transfer', 'card', 'online', 'other'])->default('cash');
            $table->string('transaction_ref')->nullable();
            $table->string('receipt_no')->unique();
            $table->text('notes')->nullable();
            $table->string('received_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('fee_payments');
    }
};
