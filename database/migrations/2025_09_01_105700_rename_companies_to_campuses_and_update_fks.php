<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Rename companies -> campuses (if exists)
        if (Schema::hasTable('companies') && !Schema::hasTable('campuses')) {
            // Drop FK on users.company_id if exists (PostgreSQL-safe)
            try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_company_id_foreign'); } catch (\Throwable $e) {}

            Schema::rename('companies', 'campuses');
        }

        // 2) Ensure columns in campuses match ERD
        if (Schema::hasTable('campuses')) {
            // Rename columns using PostgreSQL-compatible SQL (no doctrine/dbal needed)
            $columns = Schema::getColumnListing('campuses');
            if (in_array('nama_perusahaan', $columns) && !in_array('nama_campus', $columns)) {
                DB::statement('ALTER TABLE campuses RENAME COLUMN nama_perusahaan TO nama_campus');
            }
            if (in_array('email_perusahaan', $columns) && !in_array('email_campus', $columns)) {
                DB::statement('ALTER TABLE campuses RENAME COLUMN email_perusahaan TO email_campus');
            }
            if (in_array('alamat_perusahaan', $columns) && !in_array('alamat_campus', $columns)) {
                DB::statement('ALTER TABLE campuses RENAME COLUMN alamat_perusahaan TO alamat_campus');
            }
            // contact_person already aligned by name in ERD
        }

        // 3) users.company_id -> campus_id and add FK to campuses
        if (Schema::hasTable('users')) {
            $userColumns = Schema::getColumnListing('users');

            if (in_array('company_id', $userColumns) && !in_array('campus_id', $userColumns)) {
                // Drop FK first if exists (PostgreSQL-safe)
                try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_company_id_foreign'); } catch (\Throwable $e) {}
                // Rename column via PostgreSQL-compatible SQL
                DB::statement('ALTER TABLE users RENAME COLUMN company_id TO campus_id');
            } elseif (!in_array('campus_id', $userColumns)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->uuid('campus_id')->nullable()->after('pembina_user_id');
                });
            }

            // Add FK to campuses
            if (Schema::hasTable('campuses') && in_array('campus_id', Schema::getColumnListing('users'))) {
                Schema::table('users', function (Blueprint $table) {
                    try { $table->foreign('campus_id')->references('id')->on('campuses')->nullOnDelete(); } catch (\Throwable $e) {}
                });
            }
        }
    }

    public function down(): void
    {
        // Reverse users.campus_id back to company_id
        if (Schema::hasTable('users')) {
            $userColumns = Schema::getColumnListing('users');
            // Drop FK first (PostgreSQL-safe)
            try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_campus_id_foreign'); } catch (\Throwable $e) {}

            if (in_array('campus_id', $userColumns) && !in_array('company_id', $userColumns)) {
                DB::statement('ALTER TABLE users RENAME COLUMN campus_id TO company_id');
            }

            // Re-add FK to companies if table exists
            if (Schema::hasTable('companies') && in_array('company_id', Schema::getColumnListing('users'))) {
                Schema::table('users', function (Blueprint $table) {
                    try { $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete(); } catch (\Throwable $e) {}
                });
            }
        }

        // Rename campuses columns back
        if (Schema::hasTable('campuses')) {
            $columns = Schema::getColumnListing('campuses');
            if (in_array('nama_campus', $columns) && !in_array('nama_perusahaan', $columns)) {
                DB::statement('ALTER TABLE campuses RENAME COLUMN nama_campus TO nama_perusahaan');
            }
            if (in_array('email_campus', $columns) && !in_array('email_perusahaan', $columns)) {
                DB::statement('ALTER TABLE campuses RENAME COLUMN email_campus TO email_perusahaan');
            }
            if (in_array('alamat_campus', $columns) && !in_array('alamat_perusahaan', $columns)) {
                DB::statement('ALTER TABLE campuses RENAME COLUMN alamat_campus TO alamat_perusahaan');
            }
        }

        // Rename campuses -> companies
        if (Schema::hasTable('campuses') && !Schema::hasTable('companies')) {
            Schema::rename('campuses', 'companies');
        }
    }
};
