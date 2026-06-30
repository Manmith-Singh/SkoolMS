<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave'])->default('present');
            $table->text('remarks')->nullable();
            $table->string('marked_by')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('staff_attendance');
    }
};
