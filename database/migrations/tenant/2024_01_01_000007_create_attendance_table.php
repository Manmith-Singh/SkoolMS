<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present');
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index(['class_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('attendance');
    }
};
