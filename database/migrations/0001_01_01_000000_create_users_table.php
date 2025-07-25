<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('account_created_by', ['self', 'father', 'mother', 'sibling', 'relative', 'agent'])->default('self');
            $table->string('password');
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');

    }
};
