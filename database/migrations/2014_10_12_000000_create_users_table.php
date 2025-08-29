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
            // UUID primary key
            $table->uuid('id')->primary();

            // Role enum
            $table->enum('role', ['admin', 'dosen', 'pembina', 'mahasiswa']);

            // Credentials
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->rememberToken();

            // Relations among roles and company link
            $table->uuid('dosen_user_id')->nullable(); // -> users.id (role=dosen)
            $table->uuid('pembina_user_id')->nullable(); // -> users.id (role=pembina)
            $table->uuid('company_id')->nullable(); // -> companies.id (FK added after companies table is created)

            // Timestamps with timezone
            $table->timestampsTz();
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

