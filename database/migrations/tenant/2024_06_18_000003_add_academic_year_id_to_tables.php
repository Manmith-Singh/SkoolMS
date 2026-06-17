<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add columns as nullable first
        Schema::connection('tenant')->table('classes', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->boolean('is_final_year')->after('description')->default(false);
        });

        Schema::connection('tenant')->table('subjects', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        Schema::connection('tenant')->table('exams', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        Schema::connection('tenant')->table('attendance', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        Schema::connection('tenant')->table('fees', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        Schema::connection('tenant')->table('fee_categories', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        Schema::connection('tenant')->table('exam_types', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        // 2. Seed a "Legacy" academic year and assign all existing rows to it
        $legacyId = DB::connection('tenant')->table('academic_years')->insertGetId([
            'year_label' => 'Legacy',
            'start_date' => '2020-01-01',
            'end_date'   => '2025-12-31',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('classes')->update(['academic_year_id' => $legacyId]);
        DB::connection('tenant')->table('subjects')->update(['academic_year_id' => $legacyId]);
        DB::connection('tenant')->table('exams')->update(['academic_year_id' => $legacyId]);
        DB::connection('tenant')->table('attendance')->update(['academic_year_id' => $legacyId]);
        DB::connection('tenant')->table('fees')->update(['academic_year_id' => $legacyId]);
        DB::connection('tenant')->table('fee_categories')->update(['academic_year_id' => $legacyId]);
        DB::connection('tenant')->table('exam_types')->update(['academic_year_id' => $legacyId]);
    }

    public function down(): void
    {
        $tables = ['classes', 'subjects', 'exams', 'attendance', 'fees', 'fee_categories', 'exam_types'];
        foreach ($tables as $table) {
            Schema::connection('tenant')->table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('academic_year_id');
            });
        }

        Schema::connection('tenant')->table('classes', function (Blueprint $t) {
            $t->dropColumn('is_final_year');
        });
    }
};
