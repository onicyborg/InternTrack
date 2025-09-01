<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table alterations per ERD
        Schema::table('users', function (Blueprint $table) {
            // Add is_active if not exists
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->nullable()->after('password');
            }
        });

        // Adjust enum-like constraint for role (PostgreSQL): add company_admin and ensure allowed set
        if (Schema::hasColumn('users', 'role')) {
            // 1) Update existing data 'admin' -> 'company_admin'
            try {
                DB::table('users')->where('role', 'admin')->update(['role' => 'company_admin']);
            } catch (\Throwable $e) {}

            // 2) For PostgreSQL: drop existing CHECK constraint then add new one
            try {
                // Ensure column is VARCHAR wide enough
                DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(32)");
            } catch (\Throwable $e) {}

            // Drop known constraint name if exists
            try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check'); } catch (\Throwable $e) {}
            // Add new CHECK constraint with desired set
            try {
                DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('company_admin','pembina','mahasiswa','dosen'))");
            } catch (\Throwable $e) {}
        }

        // Index suggestions
        Schema::table('users', function (Blueprint $table) {
            try { $table->index('role', 'users_role_index'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('users', 'campus_id')) {
                try { $table->index('campus_id', 'users_campus_id_index'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('users', 'dosen_user_id')) {
                try { $table->index('dosen_user_id', 'users_dosen_user_id_index'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('users', 'pembina_user_id')) {
                try { $table->index('pembina_user_id', 'users_pembina_user_id_index'); } catch (\Throwable $e) {}
            }
        });

        // Drop is_active from profiles if present (moved to users per ERD)
        if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'is_active')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }

    public function down(): void
    {
        // Re-add is_active to profiles if it didn't exist
        if (Schema::hasTable('profiles') && !Schema::hasColumn('profiles', 'is_active')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->boolean('is_active')->nullable()->after('whatsapp');
            });
        }

        // Remove indexes from users
        Schema::table('users', function (Blueprint $table) {
            try { $table->dropIndex('users_role_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('users_campus_id_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('users_dosen_user_id_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('users_pembina_user_id_index'); } catch (\Throwable $e) {}
        });

        // Attempt to revert role constraint (best-effort for PostgreSQL)
        try { DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check'); } catch (\Throwable $e) {}
        try {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','dosen','pembina','mahasiswa'))");
        } catch (\Throwable $e) {}

        // Drop is_active in users
        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
