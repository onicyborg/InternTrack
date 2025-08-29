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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // mahasiswa

            $table->timestampTz('checkin_at')->nullable();
            $table->timestampTz('checkout_at')->nullable();
            $table->text('photo_checkin_url')->nullable();
            $table->text('photo_checkout_url')->nullable();
            $table->text('ttd_checkin_url')->nullable();
            $table->text('ttd_checkout_url')->nullable();
            $table->boolean('is_approve_dosen')->default(false);
            $table->boolean('is_approve_pembina')->default(false);

            $table->timestampsTz();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
