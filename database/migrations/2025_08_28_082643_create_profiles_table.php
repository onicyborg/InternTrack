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
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // 1-1 with users

            // Generic across roles
            $table->string('full_name', 191)->nullable();
            $table->text('photo_url')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->boolean('is_active')->nullable();

            // DOSEN specific
            $table->string('nik', 100)->nullable();

            // MAHASISWA specific
            $table->string('nim', 100)->nullable();
            $table->string('program_studi', 150)->nullable();

            $table->timestampsTz();

            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
