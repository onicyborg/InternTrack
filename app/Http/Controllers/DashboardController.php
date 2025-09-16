<?php

namespace App\Http\Controllers;

use App\Models\Attandances;
use App\Models\Logbooks;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Campuses;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->role == 'company_admin') {
            // Totals for mahasiswa
            $mahasiswaActive = User::where('role', 'mahasiswa')->where('is_active', 1)->count();
            $mahasiswaInactive = User::where('role', 'mahasiswa')->where('is_active', 0)->count();

            // Totals for dosen
            $dosenActive = User::where('role', 'dosen')->where('is_active', 1)->count();
            $dosenInactive = User::where('role', 'dosen')->where('is_active', 0)->count();

            // Totals for pembina
            $pembinaActive = User::where('role', 'pembina')->where('is_active', 1)->count();
            $pembinaInactive = User::where('role', 'pembina')->where('is_active', 0)->count();

            // Total campuses
            $campusTotal = Campuses::count();

            return view('admin_company.index', compact(
                'mahasiswaActive',
                'mahasiswaInactive',
                'dosenActive',
                'dosenInactive',
                'pembinaActive',
                'pembinaInactive',
                'campusTotal'
            ));
        } elseif (Auth::user()->role == 'dosen') {
            // Load current dosen with relations
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->load(['profile', 'campus']);

            // Total mahasiswa binaan (mahasiswa yang memiliki dosen_user_id = dosen saat ini)
            $totalMahasiswaBinaan = User::where('role', 'mahasiswa')
                ->where('dosen_user_id', $user->id)
                ->count();

            // Absensi menunggu konfirmasi dosen (approve_dosen null atau 0) untuk mahasiswa binaan
            $pendingAbsensi = Attandances::whereHas('user', function ($q) use ($user) {
                    $q->where('dosen_user_id', $user->id);
                })
                ->where(function ($q) {
                    $q->whereNull('is_approve_dosen')->orWhere('is_approve_dosen', 0);
                })
                ->count();

            // Logbook menunggu konfirmasi dosen (approval_dosen null atau 0) untuk mahasiswa binaan
            $pendingLogbook = Logbooks::whereHas('user', function ($q) use ($user) {
                    $q->where('dosen_user_id', $user->id);
                })
                ->where(function ($q) {
                    $q->where('approval_dosen', 'pending');
                })
                ->count();

            return view('dosen.index', [
                'user' => $user,
                'totalMahasiswaBinaan' => $totalMahasiswaBinaan,
                'pendingAbsensi' => $pendingAbsensi,
                'pendingLogbook' => $pendingLogbook,
            ]);
        } elseif (Auth::user()->role == 'pembina') {
            return view('pembina.index');
        } elseif (Auth::user()->role == 'mahasiswa') {
            $user = Auth::user()->load(['profile', 'dosen.profile', 'pembina.profile', 'campus']);

            $today = Carbon::today(config('app.timezone'));
            // Cari absensi yang dibuat hari ini untuk user
            $attendanceToday = Attandances::where('user_id', $user->id)
                ->whereDate('created_at', $today->toDateString())
                ->orderByDesc('created_at')
                ->first();

            // Hitung total logbook (semua milik user)
            $totalLogbook = Logbooks::where('user_id', $user->id)->count();

            // Range perhitungan absensi: dari start_magang sampai end_magang (atau hari ini jika end null)
            $startMagang = optional($user->profile)->start_magang ? Carbon::parse($user->profile->start_magang)->startOfDay() : null;
            $endMagang = optional($user->profile)->end_magang ? Carbon::parse($user->profile->end_magang)->endOfDay() : $today->copy()->endOfDay();

            $attendanceQuery = Attandances::where('user_id', $user->id);
            if ($startMagang) {
                $attendanceQuery->whereBetween('created_at', [$startMagang, $endMagang]);
            } else {
                // jika start belum diisi, gunakan sampai end saja
                $attendanceQuery->where('created_at', '<=', $endMagang);
            }

            $totalHadir = (clone $attendanceQuery)->where('status', 'hadir')->count();
            $totalIzin = (clone $attendanceQuery)->where('status', 'izin')->count();
            $totalSakit = (clone $attendanceQuery)->where('status', 'sakit')->count();

            return view('mahasiswa.index', [
                'user' => $user,
                'attendanceToday' => $attendanceToday,
                'totalLogbook' => $totalLogbook,
                'totalHadir' => $totalHadir,
                'totalIzin' => $totalIzin,
                'totalSakit' => $totalSakit,
            ]);
        }
    }
}
