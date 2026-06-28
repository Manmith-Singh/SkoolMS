<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('students', function (Blueprint $table) {
            $table->string('father_name', 191)->nullable()->after('guardian_phone');
            $table->string('mother_name', 191)->nullable()->after('father_name');
            $table->string('pen_id', 50)->nullable()->after('mother_name');
            $table->string('caste', 20)->nullable()->after('pen_id')
                ->comment('OC, BC-A, BC-B, BC-C, BC-D, BC-E, SC, ST, OBC');
            $table->string('aadhaar_number', 12)->nullable()->after('caste');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('students', function (Blueprint $table) {
            $table->dropColumn(['father_name', 'mother_name', 'pen_id', 'caste', 'aadhaar_number']);
        });
    }
};
