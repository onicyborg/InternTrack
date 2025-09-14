<?php

namespace App\Http\Controllers;

use App\Models\Attandances;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Mahasiswa dashboard: show user info and today's attendance status
     */

    /**
     * POST check-in: creates/updates today's attendance with checkin_at and artifacts
     */
    public function checkin(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'status' => ['required','in:hadir,izin,sakit'],
            'photo' => ['required','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'signature_base64' => ['required','string'],
        ]);

        $now = Carbon::now(config('app.timezone'));
        $attendance = Attandances::firstOrCreate(
            [
                'user_id' => $user->id,
                // kunci berdasarkan tanggal: cari record yang dibuat hari ini
            ],
            []
        );

        // Pastikan ini untuk hari ini: jika record lama, buat baru
        if (!$attendance->created_at || $attendance->created_at->timezone(config('app.timezone'))->toDateString() !== $now->toDateString()) {
            $attendance = Attandances::create(['user_id' => $user->id]);
        }

        // Set checkin jika belum
        if (empty($attendance->checkin_at)) {
            $attendance->checkin_at = $now;
        }
        $attendance->status = $request->input('status');

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('attendance/photos', 'public');
            $attendance->photo_checkin_url = Storage::url($path);
        }

        // Handle signature base64
        if ($request->filled('signature_base64')) {
            $sig = $request->input('signature_base64');
            $saved = $this->storeBase64Image($sig, 'attendance/signatures');
            if ($saved) {
                $attendance->ttd_checkin_url = Storage::url($saved);
            }
        }

        $attendance->save();

        return response()->json([
            'message' => 'Check-in berhasil',
            'data' => $attendance,
        ]);
    }

    /**
     * POST check-out: updates today's attendance with checkout_at and artifacts
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'signature_base64' => ['nullable','string'],
        ]);

        $now = Carbon::now(config('app.timezone'));
        $attendance = Attandances::where('user_id', $user->id)
            ->whereDate('created_at', $now->toDateString())
            ->latest('created_at')
            ->first();

        if (!$attendance || empty($attendance->checkin_at)) {
            return response()->json([
                'message' => 'Belum melakukan check-in',
            ], 422);
        }

        if (empty($attendance->checkout_at)) {
            $attendance->checkout_at = $now;
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('attendance/photos', 'public');
            $attendance->photo_checkout_url = Storage::url($path);
        }

        if ($request->filled('signature_base64')) {
            $sig = $request->input('signature_base64');
            $saved = $this->storeBase64Image($sig, 'attendance/signatures');
            if ($saved) {
                $attendance->ttd_checkout_url = Storage::url($saved);
            }
        }

        $attendance->save();

        return response()->json([
            'message' => 'Check-out berhasil',
            'data' => $attendance,
        ]);
    }

    private function storeBase64Image(?string $dataUri, string $dir): ?string
    {
        if (!$dataUri) return null;
        if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $dataUri, $m)) {
            return null;
        }
        $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $data = substr($dataUri, strpos($dataUri, ',') + 1);
        $bin = base64_decode($data);
        if ($bin === false) return null;
        $filename = $dir . '/' . uniqid('sig_', true) . '.' . $ext;
        Storage::disk('public')->put($filename, $bin);
        return $filename;
    }

    /**
     * GET: Attendance history page (Mahasiswa)
     */
    public function history()
    {
        return view('mahasiswa.attendance');
    }

    /**
     * GET: Attendance history data (JSON) with date range and gaps as "tanpa_keterangan".
     */
    public function historyData(Request $request)
    {
        $user = Auth::user();
        $user->load('profile');
        $profile = $user->profile;
        $today = Carbon::today(config('app.timezone'));

        // If start_magang not set or today earlier than start_magang => return empty
        if (empty($profile?->start_magang)) {
            return response()->json(['data' => []]);
        }
        $start = Carbon::parse($profile->start_magang)->startOfDay();
        if ($today->lt($start)) {
            return response()->json(['data' => []]);
        }
        $endBound = $today->copy()->endOfDay();
        if (!empty($profile->end_magang)) {
            $endCandidate = Carbon::parse($profile->end_magang)->endOfDay();
            if ($today->gt($endCandidate)) {
                $endBound = $endCandidate;
            }
        }

        // Fetch attendances in range and group by date (Y-m-d)
        $rows = Attandances::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $endBound])
            ->orderBy('created_at', 'asc')
            ->get();
        $byDate = [];
        foreach ($rows as $r) {
            $d = Carbon::parse($r->created_at)->timezone(config('app.timezone'))->toDateString();
            $byDate[$d] = $r;
        }

        // Build full date period inclusive
        $data = [];
        $cursor = $start->copy();
        while ($cursor->lte($endBound)) {
            $dateKey = $cursor->toDateString();
            $record = $byDate[$dateKey] ?? null;
            $has = $record !== null;
            $dateLabel = $cursor->copy()->locale('id')->translatedFormat('l d F Y');
            // Determine status and approvals with special handling for today when no record
            $isToday = ($dateKey === $today->toDateString());
            $status = $has && $record->status ? $record->status : ($isToday ? 'Belum Absen' : 'tanpa_keterangan');
            $approvePembina = $has
                ? (($record->is_approve_pembina ?? false) ? 'Approve' : 'Pending')
                : ($isToday ? '-' : 'Approve');
            $approveDosen = $has
                ? (($record->is_approve_dosen ?? false) ? 'Approve' : 'Pending')
                : ($isToday ? '-' : 'Approve');
            $item = [
                'date' => $dateKey,
                'date_label' => $dateLabel,
                'checkin_at' => $has && $record->checkin_at ? Carbon::parse($record->checkin_at)->timezone(config('app.timezone'))->format('H:i:s') : '-',
                'checkout_at' => $has && $record->checkout_at ? Carbon::parse($record->checkout_at)->timezone(config('app.timezone'))->format('H:i:s') : '-',
                'status' => $status,
                'approve_pembina' => $approvePembina,
                'approve_dosen' => $approveDosen,
                'photo_checkin_url' => $record->photo_checkin_url ?? null,
                'ttd_checkin_url' => $record->ttd_checkin_url ?? null,
                'photo_checkout_url' => $record->photo_checkout_url ?? null,
                'ttd_checkout_url' => $record->ttd_checkout_url ?? null,
                'action_enabled' => $has && ($record->ttd_checkin_url || $record->photo_checkin_url || $record->ttd_checkout_url || $record->photo_checkout_url),
            ];
            $data[] = $item;
            $cursor->addDay();
        }

        // Ensure ascending order by date (already by loop), but keep explicit
        usort($data, fn($a,$b)=> -strcmp($a['date'], $b['date']));

        return response()->json(['data' => $data]);
    }
}
