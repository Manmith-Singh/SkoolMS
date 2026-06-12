<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // tuition, transport, library, exam, etc.
            $table->text('description')->nullable();
            $table->decimal('default_amount', 10, 2)->default(0);
            $table->enum('frequency', ['one_time', 'monthly', 'quarterly', 'half_yearly', 'annually'])
                ->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('fee_categories');
    }
};
