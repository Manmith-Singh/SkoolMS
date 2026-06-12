<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_no')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('roll_no')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->date('admission_date')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('photo_path')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Optional link to master.users.id');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('students');
    }
};
