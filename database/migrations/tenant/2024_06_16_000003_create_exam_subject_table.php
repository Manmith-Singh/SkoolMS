<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('exam_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->unique(['exam_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('exam_subject');
    }
};
