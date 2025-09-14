<?php

namespace App\Http\Controllers;

use App\Models\Logbooks;
use App\Models\LogbooksAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogbookController extends Controller
{
    public function index()
    {
        $logs = Logbooks::with('attachments')
            ->where('user_id', auth()->id())
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return view('mahasiswa.logbooks', [
            'logbooks' => $logs,
        ]);
    }

    public function destroy(string $id)
    {
        $log = Logbooks::with('attachments')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // Hapus file fisik lampiran terlebih dahulu
        foreach ($log->attachments as $att) {
            $url = $att->filename; // contoh: /storage/logbooks/attachments/xxx.pdf
            $relative = str_starts_with($url, '/storage/') ? substr($url, 9) : ltrim(str_replace(Storage::url(''), '', $url), '/');
            try {
                if ($relative) {
                    Storage::disk('public')->delete($relative);
                }
            } catch (\Throwable $e) {
                // abaikan
            }
        }

        // Hapus record (attachments ikut terhapus via FK cascadeOnDelete jika diset, namun kita sudah handle manual juga)
        $log->delete();

        return response()->json([
            'message' => 'Logbook berhasil dihapus'
        ]);
    }

    public function show(string $id)
    {
        $log = Logbooks::with('attachments')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // Tambahkan info size (bytes) dan original_name untuk tiap attachment agar UI bisa menampilkan ukuran & tombol unduh.
        $log->attachments->transform(function (LogbooksAttachments $att) {
            $url = $att->filename; // contoh: /storage/logbooks/attachments/xxx.pdf
            $relative = str_starts_with($url, '/storage/') ? substr($url, 9) : ltrim(str_replace(Storage::url(''), '', $url), '/');
            $size = null;
            try {
                if ($relative) {
                    $size = Storage::disk('public')->size($relative);
                }
            } catch (\Throwable $e) {
                $size = null;
            }
            // Set atribut dinamis supaya ikut di-serialize ke JSON
            $att->setAttribute('size', $size);
            $att->setAttribute('original_name', basename(parse_url($url, PHP_URL_PATH)));
            return $att;
        });

        return response()->json([
            'data' => $log,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $log = Logbooks::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
            'subject'      => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'attachments'  => ['sometimes', 'array'],
            'attachments.*'=> ['file', 'mimes:jpg,jpeg,png,webp,gif,pdf', 'max:5120'],
            'removed_attachments' => ['sometimes', 'array'],
            'removed_attachments.*' => ['string'],
        ]);

        $log->update([
            'subject'     => $validated['subject'],
            'description' => $validated['description'],
            'start_date'  => $validated['start_date'],
            'end_date'    => $validated['end_date'],
        ]);

        // Hapus lampiran existing yang dipilih user (berdasarkan id atau path url)
        $removed = collect($request->input('removed_attachments', []))
            ->filter(fn($v) => filled($v))
            ->values();
        if ($removed->isNotEmpty()) {
            $attachments = $log->attachments()->get();
            foreach ($attachments as $att) {
                $match = $removed->first(function($val) use ($att) {
                    // bisa berupa id UUID atau path/url yang tersimpan di kolom filename
                    return $val === $att->id || $val === $att->filename;
                });
                if ($match) {
                    // Hapus file fisik jika masih ada
                    $url = $att->filename; // contoh: /storage/logbooks/attachments/xxx.pdf
                    $relative = str_starts_with($url, '/storage/') ? substr($url, 9) : ltrim(str_replace(Storage::url(''), '', $url), '/');
                    try {
                        if ($relative) {
                            Storage::disk('public')->delete($relative);
                        }
                    } catch (\Throwable $e) {
                        // abaikan error delete file agar tidak menghalangi proses
                    }
                    $att->delete();
                }
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('logbooks/attachments', 'public');
                LogbooksAttachments::create([
                    'logbook_id' => $log->id,
                    'filename'   => Storage::url($path),
                ]);
            }
        }

        return response()->json([
            'message' => 'Logbook berhasil diperbarui',
            'data'    => $log->load('attachments'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
            'subject'      => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'attachments'  => ['sometimes', 'array'],
            'attachments.*'=> ['file', 'mimes:jpg,jpeg,png,webp,gif,pdf', 'max:5120'],
        ]);

        $log = Logbooks::create([
            'user_id'          => $user->id,
            'subject'          => $validated['subject'],
            'description'      => $validated['description'],
            'start_date'       => $validated['start_date'],
            'end_date'         => $validated['end_date'],
            'approval_dosen'   => 'pending',
            'approval_pembina' => 'pending',
        ]);

        // Simpan multiple attachments (jika ada)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('logbooks/attachments', 'public');
                LogbooksAttachments::create([
                    'logbook_id' => $log->id,
                    'filename'   => Storage::url($path),
                ]);
            }
        }

        return response()->json([
            'message' => 'Logbook berhasil ditambahkan',
            'data'    => $log->load('attachments'),
        ]);
    }
}
