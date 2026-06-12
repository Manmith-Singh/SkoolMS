<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('fee_categories')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'waived'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('fees');
    }
};
