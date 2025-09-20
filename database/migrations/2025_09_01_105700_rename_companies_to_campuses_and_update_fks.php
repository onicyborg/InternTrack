<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1) Rename companies -> campuses (if exists)
        if (Schema::hasTable('companies') && !Schema::hasTable('campuses')) {
            // Pastikan FK dari users.company_id ke companies sudah dilepas sebelum rename
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'company_id')) {
                // Jalankan HANYA pernyataan yang sesuai engine untuk menghindari abort transaction
                if ($driver === 'pgsql') {
                    try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_company_id_foreign'); } catch (\Throwable $e) {}
                } else {
                    // Cari nama constraint FK secara dinamis lalu drop
                    try {
                        $name = DB::table('information_schema.KEY_COLUMN_USAGE')
                            ->where('TABLE_SCHEMA', DB::getDatabaseName())
                            ->where('TABLE_NAME', 'users')
                            ->where('COLUMN_NAME', 'company_id')
                            ->whereNotNull('REFERENCED_TABLE_NAME')
                            ->value('CONSTRAINT_NAME');
                        if ($name) {
                            DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `{$name}`");
                        }
                    } catch (\Throwable $e) {}
                }
            }

            // Rename table dengan guard try/catch agar tidak meng-abort transaksi bila kondisi berubah
            try { Schema::rename('companies', 'campuses'); } catch (\Throwable $e) {}
        }

        // 2) Ensure columns in campuses match ERD
        if (Schema::hasTable('campuses')) {
            $columns = Schema::getColumnListing('campuses');
            // Helper for MySQL: add-copy-drop rename
            $renamePortable = function(string $from, string $to) use ($driver) {
                if (!Schema::hasColumn('campuses', $from) || Schema::hasColumn('campuses', $to)) return;
                if ($driver === 'pgsql') {
                    try { DB::statement("ALTER TABLE campuses RENAME COLUMN $from TO $to"); } catch (\Throwable $e) {}
                } else { // mysql
                    // Tambah kolom baru string nullable, salin data, hapus kolom lama
                    Schema::table('campuses', function (Blueprint $table) use ($to) { $table->string($to)->nullable(); });
                    try { DB::table('campuses')->update([$to => DB::raw($from)]); } catch (\Throwable $e) {}
                    Schema::table('campuses', function (Blueprint $table) use ($from) { try { $table->dropColumn($from); } catch (\Throwable $e) {} });
                }
            };

            $renamePortable('nama_perusahaan', 'nama_campus');
            $renamePortable('email_perusahaan', 'email_campus');
            $renamePortable('alamat_perusahaan', 'alamat_campus');
            // contact_person already aligned by name in ERD
        }

        // 3) users.company_id -> campus_id and add FK to campuses
        if (Schema::hasTable('users')) {
            $userColumns = Schema::getColumnListing('users');

            if (in_array('company_id', $userColumns) && !in_array('campus_id', $userColumns)) {
                // Drop FK first if exists
                if ($driver === 'pgsql') {
                    try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_company_id_foreign'); } catch (\Throwable $e) {}
                } else {
                    try { DB::statement('ALTER TABLE `users` DROP FOREIGN KEY `users_company_id_foreign`'); } catch (\Throwable $e) {}
                }

                if ($driver === 'pgsql') {
                    try { DB::statement('ALTER TABLE users RENAME COLUMN company_id TO campus_id'); } catch (\Throwable $e) {}
                } else { // mysql add-copy-drop
                    Schema::table('users', function (Blueprint $table) { $table->uuid('campus_id')->nullable()->after('pembina_user_id'); });
                    try { DB::table('users')->update(['campus_id' => DB::raw('company_id')]); } catch (\Throwable $e) {}
                    Schema::table('users', function (Blueprint $table) { try { $table->dropColumn('company_id'); } catch (\Throwable $e) {} });
                }
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
        $driver = DB::getDriverName();
        // Reverse users.campus_id back to company_id
        if (Schema::hasTable('users')) {
            $userColumns = Schema::getColumnListing('users');
            // Drop FK first sesuai engine
            if ($driver === 'pgsql') {
                try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_campus_id_foreign'); } catch (\Throwable $e) {}
            } else {
                try { DB::statement('ALTER TABLE `users` DROP FOREIGN KEY `users_campus_id_foreign`'); } catch (\Throwable $e) {}
            }

            if (in_array('campus_id', $userColumns) && !in_array('company_id', $userColumns)) {
                if ($driver === 'pgsql') {
                    try { DB::statement('ALTER TABLE users RENAME COLUMN campus_id TO company_id'); } catch (\Throwable $e) {}
                } else {
                    Schema::table('users', function (Blueprint $table) { $table->uuid('company_id')->nullable()->after('pembina_user_id'); });
                    try { DB::table('users')->update(['company_id' => DB::raw('campus_id')]); } catch (\Throwable $e) {}
                    Schema::table('users', function (Blueprint $table) { try { $table->dropColumn('campus_id'); } catch (\Throwable $e) {} });
                }
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
            $renamePortable = function(string $from, string $to) use ($driver) {
                if (!Schema::hasColumn('campuses', $from) || Schema::hasColumn('campuses', $to)) return;
                if ($driver === 'pgsql') {
                    try { DB::statement("ALTER TABLE campuses RENAME COLUMN $from TO $to"); } catch (\Throwable $e) {}
                } else {
                    Schema::table('campuses', function (Blueprint $table) use ($to) { $table->string($to)->nullable(); });
                    try { DB::table('campuses')->update([$to => DB::raw($from)]); } catch (\Throwable $e) {}
                    Schema::table('campuses', function (Blueprint $table) use ($from) { try { $table->dropColumn($from); } catch (\Throwable $e) {} });
                }
            };
            $renamePortable('nama_campus', 'nama_perusahaan');
            $renamePortable('email_campus', 'email_perusahaan');
            $renamePortable('alamat_campus', 'alamat_perusahaan');
        }

        // Rename campuses -> companies
        if (Schema::hasTable('campuses') && !Schema::hasTable('companies')) {
            Schema::rename('campuses', 'companies');
        }
    }
};
