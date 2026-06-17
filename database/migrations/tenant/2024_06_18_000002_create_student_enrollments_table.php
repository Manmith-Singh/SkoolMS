<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('roll_no', 20)->nullable();
            $table->string('status', 20)->default('active')
                ->comment('active, graduated, transferred, dropped');
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('student_enrollments');
    }
};
