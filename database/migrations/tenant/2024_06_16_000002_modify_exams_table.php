<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('exams', function (Blueprint $table) {
            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types')->nullOnDelete();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();

            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
            $table->dropColumn('date');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('exams', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date')->nullable();

            $table->dropForeign(['exam_type_id']);
            $table->dropColumn('exam_type_id');
            $table->dropColumn('from_date');
            $table->dropColumn('to_date');
        });
    }
};
