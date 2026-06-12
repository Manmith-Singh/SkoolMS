<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // e.g. "Grade 5"
            $table->string('section')->nullable(); // e.g. "A"
            $table->unsignedInteger('capacity')->default(40);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['name', 'section']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('classes');
    }
};
