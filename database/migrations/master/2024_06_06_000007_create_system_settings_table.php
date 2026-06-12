<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');        // general, email, sms, branding, billing, etc.
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'boolean', 'integer', 'json'])->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);       // safe to expose to tenant
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('system_settings');
    }
};
