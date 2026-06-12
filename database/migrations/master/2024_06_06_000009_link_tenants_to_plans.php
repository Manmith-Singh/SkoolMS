<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Many SaaS controls are pre-computed on the tenant row.
        Schema::connection('mysql')->table('tenants', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('status')->constrained('subscription_plans')->nullOnDelete();
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('subscription_ends_at');
        });
    }
};
