<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attandances;
use Carbon\Carbon;

class AttendancePembinaController extends Controller
{
    /**
     * Tampilkan daftar mahasiswa binaan untuk kelola/verifikasi absensi (role pembina).
     */
    public function index()
    {
        /** @var \App\Models\User $pembina */
        $pembina = Auth::user();

        // Ambil mahasiswa binaan beserta profilnya
        $students = User::with('profile')
            ->where('role', 'mahasiswa')
            ->where('pembina_user_id', $pembina->id)
            ->get();

        // Hitung absensi pending per mahasiswa (menunggu approval pembina)
        $pendingMap = Attandances::selectRaw('user_id, COUNT(*) as cnt')
            ->whereIn('user_id', $students->pluck('id'))
            ->where(function ($q) {
                $q->whereNull('is_approve_pembina')->orWhere('is_approve_pembina', 0);
            })
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        return view('pembina.attendance', [
            'students' => $students,
            'pendingMap' => $pendingMap,
        ]);
    }

    /**
     * Detail attendance untuk 1 mahasiswa binaan (role pembina).
     */
    public function show(string $id)
    {
        /** @var \App\Models\User $pembina */
        $pembina = Auth::user();

        // Pastikan mahasiswa tsb adalah binaan pembina yang login
        $student = User::with(['profile', 'campus', 'dosen.profile'])
            ->where('id', $id)
            ->where('role', 'mahasiswa')
            ->where('pembina_user_id', $pembina->id)
            ->firstOrFail();

        $today = Carbon::today(config('app.timezone'));

        // Jika belum ada tanggal mulai magang, tampilkan kosong
        $profile = $student->profile;
        $rows = [];
        if (!empty($profile?->start_magang)) {
            $start = Carbon::parse($profile->start_magang)->startOfDay();
            // endBound: hari ini atau end_magang jika sudah lewat
            $endBound = $today->copy()->endOfDay();
            if (!empty($profile->end_magang)) {
                $endCandidate = Carbon::parse($profile->end_magang)->endOfDay();
                if ($today->gt($endCandidate)) {
                    $endBound = $endCandidate;
                }
            }

            // Fetch attendances in range and group by date
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

                $approvePembina = $has
                    ? ((($record->is_approve_pembina ?? null) === null) ? 'Pending' : ($record->is_approve_pembina ? 'Approve' : 'Pending'))
                    : ($isToday ? '-' : 'Approve');
                $approveDosen = $has
                    ? ((($record->is_approve_dosen ?? null) === null) ? 'Pending' : ($record->is_approve_dosen ? 'Approve' : 'Pending'))
                    : ($isToday ? '-' : 'Approve');

                $rows[] = [
                    'id' => $has ? $record->id : null,
                    'date' => $dateKey,
                    'date_label' => $dateLabel,
                    'checkin_at' => $has && $record->checkin_at ? Carbon::parse($record->checkin_at)->timezone(config('app.timezone'))->format('H:i:s') : '-',
                    'checkout_at' => $has && $record->checkout_at ? Carbon::parse($record->checkout_at)->timezone(config('app.timezone'))->format('H:i:s') : '-',
                    'status' => $rawStatus,
                    'status_label' => $statusLabel,
                    'approve_pembina' => $approvePembina,
                    'approve_dosen' => $approveDosen,
                    'photo_checkin_url' => $record->photo_checkin_url ?? null,
                    'ttd_checkin_url' => $record->ttd_checkin_url ?? null,
                    'photo_checkout_url' => $record->photo_checkout_url ?? null,
                    'ttd_checkout_url' => $record->ttd_checkout_url ?? null,
                ];
                $cursor->addDay();
            }

            // sort descending by date to match previous UI
            usort($rows, fn($a,$b)=> -strcmp($a['date'], $b['date']));
        }

        $pendingCount = Attandances::where('user_id', $student->id)
            ->where(function ($q) {
                $q->whereNull('is_approve_pembina')->orWhere('is_approve_pembina', 0);
            })
            ->count();


        return view('pembina.attendance_detail', [
            'student' => $student,
            'rows' => $rows,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve absensi oleh pembina.
     */
    public function approve(Request $request, string $attendanceId)
    {
        $pembina = Auth::user();

        $attendance = Attandances::findOrFail($attendanceId);

        // Pastikan absensi milik mahasiswa binaan pembina yang login
        $student = User::where('id', $attendance->user_id)
            ->where('role', 'mahasiswa')
            ->where('pembina_user_id', $pembina->id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attendance->is_approve_pembina = 1;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Absensi telah disetujui',
        ]);
    }
}
