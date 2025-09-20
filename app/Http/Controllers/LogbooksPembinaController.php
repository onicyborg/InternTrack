<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Logbooks;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LogbooksPembinaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $pembina */
        $pembina = Auth::user();

        // Ambil mahasiswa binaan beserta profilnya
        $students = User::with('profile')
            ->where('role', 'mahasiswa')
            ->where('pembina_user_id', $pembina->id)
            ->get();

        // Hitung jumlah logbook pending approval pembina per mahasiswa
        $pendingMap = Logbooks::selectRaw('user_id, COUNT(*) as cnt')
            ->whereIn('user_id', $students->pluck('id'))
            ->where(function($q){
                $q->where('approval_pembina', 'pending')
                  ->orWhere('approval_pembina', 'reject');
            })
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        return view('pembina.logbooks_approval', [
            'students' => $students,
            'pendingMap' => $pendingMap,
        ]);
    }

    /**
     * Tampilkan daftar logbook untuk satu mahasiswa binaan pembina.
     */
    public function show(string $userId)
    {
        /** @var \App\Models\User $pembina */
        $pembina = Auth::user();

        // Pastikan user adalah mahasiswa binaan pembina yang login
        $student = User::with('profile')
            ->where('id', $userId)
            ->where('role', 'mahasiswa')
            ->where('pembina_user_id', $pembina->id)
            ->firstOrFail();

        $logbooks = Logbooks::where('user_id', $student->id)
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return view('pembina.logbooks_approval_detail', [
            'student' => $student,
            'logbooks' => $logbooks,
        ]);
    }

    /**
     * Detail 1 logbook (JSON) milik mahasiswa binaan pembina, beserta attachments.
     */
    public function logbookDetail(string $userId, string $logbookId)
    {
        $pembina = Auth::user();

        // Validasi mahasiswa binaan
        $student = User::where('id', $userId)
            ->where('role', 'mahasiswa')
            ->where('pembina_user_id', $pembina->id)
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
     * Keputusan approve/reject oleh pembina untuk logbook tertentu.
     */
    public function decide(Request $request, string $logbookId)
    {
        $pembina = Auth::user();

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remark'   => ['nullable', 'string'],
        ]);

        $log = Logbooks::with('user')
            ->where('id', $logbookId)
            ->firstOrFail();

        // Pastikan logbook milik mahasiswa binaan pembina
        $student = $log->user;
        if (!$student || $student->role !== 'mahasiswa' || $student->pembina_user_id !== $pembina->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $log->approval_pembina = $validated['decision'];
        $log->remark_pembina = $validated['remark'] ?? null;
        $log->save();

        return response()->json([
            'success' => true,
            'message' => 'Keputusan berhasil disimpan',
            'data' => $log,
        ]);
    }

    /**
     * Unduh semua lampiran sebagai ZIP (pembina).
     */
    public function downloadZip(string $logbookId)
    {
        $pembina = Auth::user();

        $log = Logbooks::with(['attachments', 'user'])
            ->where('id', $logbookId)
            ->firstOrFail();

        $student = $log->user;
        if (!$student || $student->role !== 'mahasiswa' || $student->pembina_user_id !== $pembina->id) {
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
