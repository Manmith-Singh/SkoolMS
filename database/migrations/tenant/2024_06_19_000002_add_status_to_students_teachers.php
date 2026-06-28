<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('students', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('aadhaar_number');
        });

        Schema::connection('tenant')->table('teachers', function (Blueprint $table) {
            $table->string('status', 20)->default('working')->after('salary');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('students', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::connection('tenant')->table('teachers', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
