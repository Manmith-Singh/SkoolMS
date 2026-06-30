<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('teachers', function (Blueprint $table) {
            $table->string('pf_number', 50)->nullable()->after('salary');
            $table->string('esi_number', 50)->nullable()->after('pf_number');
            $table->string('uan_number', 50)->nullable()->after('esi_number');
            $table->string('bank_account', 30)->nullable()->after('uan_number');
            $table->string('ifsc_code', 20)->nullable()->after('bank_account');
            $table->decimal('basic_pay', 10, 2)->nullable()->after('ifsc_code');
            $table->decimal('hra', 10, 2)->nullable()->after('basic_pay');
            $table->decimal('da', 10, 2)->nullable()->after('hra');
            $table->decimal('conveyance', 10, 2)->nullable()->after('da');
            $table->decimal('medical_allowance', 10, 2)->nullable()->after('conveyance');
            $table->decimal('other_allowances', 10, 2)->nullable()->after('medical_allowance');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'pf_number', 'esi_number', 'uan_number', 'bank_account', 'ifsc_code',
                'basic_pay', 'hra', 'da', 'conveyance', 'medical_allowance', 'other_allowances',
            ]);
        });
    }
};
