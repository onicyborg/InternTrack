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
        Schema::create('logbook_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('logbook_id');
            $table->string('filename', 255);
            $table->timestampsTz();

            $table->foreign('logbook_id')->references('id')->on('logbooks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_attachments');
    }
};
