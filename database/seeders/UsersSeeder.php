<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profiles;
use App\Models\Campuses;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Helper dates for mahasiswa magang period
        $startMagang = Carbon::now()->startOfMonth()->toDateString();
        $endMagang = Carbon::now()->startOfMonth()->addMonthsNoOverflow(3)->endOfMonth()->toDateString();

        // 1) Company Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@company.test'],
            [
                'role' => 'company_admin',
                'password' => 'Qwerty123*', // hashed by casts
                'is_active' => true,
                'remember_token' => Str::random(60),
            ]
        );
        Profiles::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'full_name' => 'Admin Company',
                'photo_url' => null,
                'phone' => null,
                'whatsapp' => null,
            ]
        );

        // Create two campuses
        $campusA = Campuses::updateOrCreate(
            ['nama_campus' => 'Campus Alpha Interns'],
            [
                'contact_person' => 'CP Alpha',
                'email_campus' => 'alpha@campus.test',
                'alamat_campus' => 'Jl. Alpha No. 1, Jakarta',
            ]
        );
        $campusB = Campuses::updateOrCreate(
            ['nama_campus' => 'Campus Beta Interns'],
            [
                'contact_person' => 'CP Beta',
                'email_campus' => 'beta@campus.test',
                'alamat_campus' => 'Jl. Beta No. 2, Bandung',
            ]
        );

        // 2) Two Lecturers (Dosen)
        $dosen = [];
        for ($i = 1; $i <= 2; $i++) {
            $u = User::updateOrCreate(
                ['email' => "dosen{$i}@test.com"],
                [
                    'role' => 'dosen',
                    'password' => 'Qwerty123*',
                    'is_active' => true,
                    'remember_token' => Str::random(60),
                    'campus_id' => $i === 1 ? $campusA->id : $campusB->id,
                ]
            );
            Profiles::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'full_name' => "Dosen {$i}",
                    'photo_url' => null,
                    'phone' => null,
                    'whatsapp' => null,
                ]
            );
            $dosen[] = $u;
        }

        // 3) Two Mentors (Pembina)
        $pembina = [];
        for ($i = 1; $i <= 2; $i++) {
            $u = User::updateOrCreate(
                ['email' => "pembina{$i}@test.com"],
                [
                    'role' => 'pembina',
                    'password' => 'Qwerty123*',
                    'is_active' => true,
                    'remember_token' => Str::random(60),
                    'campus_id' => $i === 1 ? $campusA->id : $campusB->id,
                ]
            );
            Profiles::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'full_name' => "Pembina {$i}",
                    'photo_url' => null,
                    'phone' => null,
                    'whatsapp' => null,
                ]
            );
            $pembina[] = $u;
        }

        // 4) Ten Students (Mahasiswa), split evenly to the two dosen and two pembina
        for ($i = 1; $i <= 10; $i++) {
            $assignedDosen = $dosen[($i % 2)];      // alternate 0,1
            $assignedPembina = $pembina[($i % 2)];  // alternate 0,1

            $m = User::updateOrCreate(
                ['email' => "mhs{$i}@test.com"],
                [
                    'role' => 'mahasiswa',
                    'password' => 'Qwerty123*',
                    'is_active' => true,
                    'remember_token' => Str::random(60),
                    'dosen_user_id' => $assignedDosen->id,
                    'pembina_user_id' => $assignedPembina->id,
                    // Campus should match assigned lecturer campus to satisfy constraints
                    'campus_id' => $assignedDosen->campus_id,
                ]
            );

            Profiles::updateOrCreate(
                ['user_id' => $m->id],
                [
                    'full_name' => "Mahasiswa {$i}",
                    'photo_url' => null,
                    'nim' => str_pad((string)(240000 + $i), 8, '0', STR_PAD_LEFT),
                    'program_studi' => 'Teknik Informatika',
                    'start_magang' => $startMagang,
                    'end_magang' => $endMagang,
                ]
            );
        }
    }
}
