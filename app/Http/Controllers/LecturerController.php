<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profiles;
use App\Models\Campuses;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LecturerController extends Controller
{
    /**
     * Display lecturers list page.
     */
    public function index()
    {
        $lecturers = User::with(['profile', 'campus'])
            ->where('role', 'dosen')
            ->orderBy('email')
            ->get();
        $campuses = Campuses::orderBy('nama_campus')->get();
        return view('admin_company.manage-lecturer', compact('lecturers', 'campuses'));
    }

    /**
     * Show a single lecturer.
     */
    public function show(string $id)
    {
        $user = User::with(['profile', 'campus'])->where('role', 'dosen')->findOrFail($id);
        return response()->json([ 'data' => $user ]);
    }

    /**
     * Store a new lecturer (user + profile).
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'campus_id' => 'required|uuid|exists:campuses,id',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            // Optional password; if not provided, default will be used
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $user = new User();
        $user->role = 'dosen';
        $user->email = $payload['email'];
        $user->campus_id = $payload['campus_id'];
        $user->is_active = $payload['is_active'] ?? true;
        $user->password = Hash::make($payload['password'] ?? 'Qwert123*');
        $user->save();

        $profile = new Profiles();
        $profile->user_id = $user->id;
        $profile->full_name = $payload['full_name'];
        $profile->phone = $payload['phone'] ?? null;
        $profile->whatsapp = $payload['whatsapp'] ?? null;
        $profile->nik = $payload['nik'] ?? null;
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/profiles');
            $profile->photo_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile', 'campus']);

        return response()->json([
            'message' => 'Lecturer created successfully',
            'data' => $user,
        ]);
    }

    /**
     * Update an existing lecturer.
     */
    public function update(Request $request, string $id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $user->id . ',id',
            'campus_id' => 'required|uuid|exists:campuses,id',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $user->email = $payload['email'];
        $user->campus_id = $payload['campus_id'];
        if (array_key_exists('is_active', $payload)) {
            $user->is_active = $payload['is_active'];
        }
        if (!empty($payload['password'] ?? null)) {
            $user->password = Hash::make($payload['password']);
        }
        $user->save();

        $profile = $user->profile ?: new Profiles(['user_id' => $user->id]);
        $profile->full_name = $payload['full_name'];
        $profile->phone = $payload['phone'] ?? null;
        $profile->whatsapp = $payload['whatsapp'] ?? null;
        // Photo removal if requested
        if ($request->boolean('photo_remove')) {
            if (!empty($profile->photo_url) && str_starts_with($profile->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $profile->photo_url = null;
        }
        // Handle new photo upload
        if ($request->hasFile('photo')) {
            // Delete old file first if exists
            if (!empty($profile->photo_url) && str_starts_with($profile->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $path = $request->file('photo')->store('public/profiles');
            $profile->photo_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile', 'campus']);

        return response()->json([
            'message' => 'Lecturer updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a lecturer.
     */
    public function destroy(string $id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);
        // Delete profile first (if exists), then user
        $user->profile()?->delete();
        $user->delete();

        return response()->json([
            'message' => 'Lecturer deleted successfully',
        ]);
    }
}
