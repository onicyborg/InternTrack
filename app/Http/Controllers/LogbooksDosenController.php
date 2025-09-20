<?php

namespace App\Http\Controllers;

use App\Models\Logbooks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class LogbooksDosenController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $dosen */
        $dosen = Auth::user();

        // Ambil mahasiswa binaan beserta profilnya
        $students = User::with('profile')
            ->where('role', 'mahasiswa')
            ->where('dosen_user_id', $dosen->id)
            ->get();

        // Hitung jumlah logbook pending approval dosen per mahasiswa
        $pendingMap = Logbooks::selectRaw('user_id, COUNT(*) as cnt')
            ->whereIn('user_id', $students->pluck('id'))
            ->where('approval_dosen', 'pending')
            ->orWhere('approval_dosen', 'reject')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        return view('dosen.logbooks_approval', [
            'students' => $students,
            'pendingMap' => $pendingMap,
        ]);
    }

    /**
     * Tampilkan daftar logbook untuk satu mahasiswa binaan.
     */
    public function show(string $userId)
    {
        /** @var \App\Models\User $dosen */
        $dosen = Auth::user();

        // Pastikan user adalah mahasiswa binaan dosen yang login
        $student = User::with('profile')
            ->where('id', $userId)
            ->where('role', 'mahasiswa')
            ->where('dosen_user_id', $dosen->id)
            ->firstOrFail();

        $logbooks = Logbooks::where('user_id', $student->id)
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return view('dosen.logbooks_approval_detail', [
            'student' => $student,
            'logbooks' => $logbooks,
        ]);
    }

    /**
     * Detail 1 logbook (JSON) milik mahasiswa binaan, beserta attachments.
     */
    public function logbookDetail(string $userId, string $logbookId)
    {
        $dosen = Auth::user();

        // Validasi mahasiswa binaan
        $student = User::where('id', $userId)
            ->where('role', 'mahasiswa')
            ->where('dosen_user_id', $dosen->id)
            ->firstOrFail();

        $log = Logbooks::with('attachments')
            ->where('user_id', $student->id)
            ->where('id', $logbookId)
            ->firstOrFail();

        return response()->json([
            'data' => $log,
        ]);
    }

    /**
     * Keputusan approve/reject oleh dosen untuk logbook tertentu.
     */
    public function decide(Request $request, string $logbookId)
    {
        $dosen = Auth::user();

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remark'   => ['nullable', 'string'],
        ]);

        $log = Logbooks::with('user')
            ->where('id', $logbookId)
            ->firstOrFail();

        // Pastikan logbook milik mahasiswa binaan dosen
        $student = $log->user;
        if (!$student || $student->role !== 'mahasiswa' || $student->dosen_user_id !== $dosen->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $log->approval_dosen = $validated['decision'];
        $log->remark_dosen = $validated['remark'] ?? null;
        $log->save();

        return response()->json([
            'success' => true,
            'message' => 'Keputusan berhasil disimpan',
            'data' => $log,
        ]);
    }

    /**
     * Unduh semua lampiran sebagai ZIP.
     */
    public function downloadZip(string $logbookId)
    {
        $dosen = Auth::user();

        $log = Logbooks::with(['attachments', 'user'])
            ->where('id', $logbookId)
            ->firstOrFail();

        $student = $log->user;
        if (!$student || $student->role !== 'mahasiswa' || $student->dosen_user_id !== $dosen->id) {
            abort(403, 'Unauthorized');
        }

        // Buat zip sementara
        $zipFileName = 'logbook_attachments_'.$log->id.'.zip';
        $tmpPath = storage_path('app/tmp/'.$zipFileName);
        if (!is_dir(dirname($tmpPath))) {
            @mkdir(dirname($tmpPath), 0775, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip');
        }

        foreach ($log->attachments as $att) {
            $url = $att->filename; // contoh: /storage/logbooks/attachments/xxx.pdf
            $relative = str_starts_with($url, '/storage/') ? substr($url, 9) : ltrim(str_replace(Storage::url(''), '', $url), '/');
            if ($relative) {
                $full = storage_path('app/public/'.ltrim($relative, '/'));
                if (is_file($full)) {
                    $zip->addFile($full, basename($full));
                }
            }
        }
        $zip->close();

        return response()->download($tmpPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
