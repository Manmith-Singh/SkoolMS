<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // e.g. "Starter", "Pro", "Enterprise"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_period', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->unsignedInteger('max_students')->default(0);  // 0 = unlimited
            $table->unsignedInteger('max_teachers')->default(0);
            $table->unsignedInteger('max_storage_mb')->default(0);
            $table->json('features')->nullable();                // list of feature flags
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('subscription_plans');
    }
};
