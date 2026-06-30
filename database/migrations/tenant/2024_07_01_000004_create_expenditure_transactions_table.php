<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('expenditure_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expenditure_type_id')->constrained('expenditure_types')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('paid_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('expenditure_transactions');
    }
};
