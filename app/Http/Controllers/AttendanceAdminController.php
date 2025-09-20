<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attandances;
use Carbon\Carbon;

class AttendanceAdminController extends Controller
{
    /**
     * Tampilkan daftar seluruh mahasiswa untuk melihat absensi (read-only)
     */
    public function index()
    {
        $students = User::with('profile')
            ->where('role', 'mahasiswa')
            ->get();

        // Untuk admin read-only, kita tidak perlu status pending, set 0 semua
        $pendingMap = collect($students)->mapWithKeys(fn($u) => [$u->id => 0]);

        return view('admin_company.attendance', [
            'students' => $students,
            'pendingMap' => $pendingMap,
        ]);
    }

    /**
     * Detail attendance read-only untuk satu mahasiswa
     */
    public function show(string $id)
    {
        $student = User::with(['profile', 'campus', 'dosen.profile'])
            ->where('id', $id)
            ->where('role', 'mahasiswa')
            ->firstOrFail();

        $today = Carbon::today(config('app.timezone'));

        $profile = $student->profile;
        $rows = [];
        if (!empty($profile?->start_magang)) {
            $start = Carbon::parse($profile->start_magang)->startOfDay();
            $endBound = $today->copy()->endOfDay();
            if (!empty($profile->end_magang)) {
                $endCandidate = Carbon::parse($profile->end_magang)->endOfDay();
                if ($today->gt($endCandidate)) {
                    $endBound = $endCandidate;
                }
            }

            $records = Attandances::where('user_id', $student->id)
                ->whereBetween('created_at', [$start, $endBound])
                ->orderBy('created_at', 'asc')
                ->get();
            $byDate = [];
            foreach ($records as $r) {
                $d = Carbon::parse($r->created_at)->timezone(config('app.timezone'))->toDateString();
                $byDate[$d] = $r;
            }

            $cursor = $start->copy();
            while ($cursor->lte($endBound)) {
                $dateKey = $cursor->toDateString();
                $record = $byDate[$dateKey] ?? null;
                $has = $record !== null;
                $dateLabel = $cursor->copy()->locale('id')->translatedFormat('l d F Y');
                $isToday = ($dateKey === $today->toDateString());
                $rawStatus = $has && $record->status ? $record->status : ($isToday ? 'Belum Absen' : 'tanpa_keterangan');
                $statusLabelMap = [
                    'hadir' => 'Masuk',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'tanpa_keterangan' => 'Tanpa Keterangan',
                ];
                $statusLabel = $statusLabelMap[$rawStatus] ?? $rawStatus;

                $rows[] = [
                    'id' => $has ? $record->id : null,
                    'date' => $dateKey,
                    'date_label' => $dateLabel,
                    'checkin_at' => $has && $record->checkin_at ? Carbon::parse($record->checkin_at)->timezone(config('app.timezone'))->format('H:i:s') : '-',
                    'checkout_at' => $has && $record->checkout_at ? Carbon::parse($record->checkout_at)->timezone(config('app.timezone'))->format('H:i:s') : '-',
                    'status' => $rawStatus,
                    'status_label' => $statusLabel,
                    'photo_checkin_url' => $record->photo_checkin_url ?? null,
                    'ttd_checkin_url' => $record->ttd_checkin_url ?? null,
                    'photo_checkout_url' => $record->photo_checkout_url ?? null,
                    'ttd_checkout_url' => $record->ttd_checkout_url ?? null,
                ];
                $cursor->addDay();
            }

            // sort tanggal desc
            usort($rows, fn($a,$b)=> -strcmp($a['date'], $b['date']));
        }

        return view('admin_company.attendance_detail', [
            'student' => $student,
            'rows' => $rows,
            'pendingCount' => 0,
        ]);
    }
}
