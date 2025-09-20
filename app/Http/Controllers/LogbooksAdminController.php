<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Logbooks;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LogbooksAdminController extends Controller
{
    /**
     * Daftar seluruh mahasiswa untuk melihat logbook (read-only)
     */
    public function index()
    {
        $students = User::with('profile')
            ->where('role', 'mahasiswa')
            ->get();

        // Read-only: tidak ada pending approval untuk admin
        $pendingMap = collect($students)->mapWithKeys(fn($u) => [$u->id => 0]);

        return view('admin_company.logbooks', [
            'students' => $students,
            'pendingMap' => $pendingMap,
        ]);
    }

    /**
     * Detail: daftar logbooks untuk satu mahasiswa
     */
    public function show(string $userId)
    {
        $student = User::with('profile')
            ->where('id', $userId)
            ->where('role', 'mahasiswa')
            ->firstOrFail();

        $logbooks = Logbooks::where('user_id', $student->id)
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return view('admin_company.logbooks_detail', [
            'student' => $student,
            'logbooks' => $logbooks,
        ]);
    }

    /**
     * JSON detail logbook + attachments (read-only)
     */
    public function logbookDetail(string $userId, string $logbookId)
    {
        $student = User::where('id', $userId)
            ->where('role', 'mahasiswa')
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
     * Unduh semua lampiran sebagai ZIP (read-only, tanpa check kepemilikan)
     */
    public function downloadZip(string $logbookId)
    {
        $log = Logbooks::with(['attachments'])
            ->where('id', $logbookId)
            ->firstOrFail();

        $zipFileName = 'logbook_attachments_' . $log->id . '.zip';
        $tmpPath = storage_path('app/tmp/' . $zipFileName);
        if (!is_dir(dirname($tmpPath))) {
            @mkdir(dirname($tmpPath), 0775, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip');
        }

        foreach ($log->attachments as $att) {
            $url = $att->filename;
            $relative = str_starts_with($url, '/storage/') ? substr($url, 9) : ltrim(str_replace(Storage::url(''), '', $url), '/');
            if ($relative) {
                $full = storage_path('app/public/' . ltrim($relative, '/'));
                if (is_file($full)) {
                    $zip->addFile($full, basename($full));
                }
            }
        }

        $zip->close();
        return response()->download($tmpPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
