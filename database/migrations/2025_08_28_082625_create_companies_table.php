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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_perusahaan', 191);
            $table->string('contact_person', 191)->nullable();
            $table->string('email_perusahaan', 191)->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->timestampsTz();
        });

        // Add FK to users.company_id now that companies table exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->uuid('company_id')->nullable();
            }
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK from users first
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropForeign(['company_id']);
            }
        });
        Schema::dropIfExists('companies');
    }
};
