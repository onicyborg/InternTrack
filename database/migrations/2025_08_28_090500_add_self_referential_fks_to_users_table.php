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
        Schema::table('users', function (Blueprint $table) {
            // Add self-referential foreign keys after table exists
            if (Schema::hasColumn('users', 'dosen_user_id')) {
                $table->foreign('dosen_user_id')->references('id')->on('users')->nullOnDelete();
            }
            if (Schema::hasColumn('users', 'pembina_user_id')) {
                $table->foreign('pembina_user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'dosen_user_id')) {
                $table->dropForeign(['dosen_user_id']);
            }
            if (Schema::hasColumn('users', 'pembina_user_id')) {
                $table->dropForeign(['pembina_user_id']);
            }
        });
    }
};
