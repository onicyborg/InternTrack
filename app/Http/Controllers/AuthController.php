<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the authenticated user's profile page for Company Admin.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin_company.profile', compact('user'));
    }

    /**
     * Show the authenticated student's profile page.
     */
    public function studentProfile()
    {
        $user = Auth::user();
        return view('mahasiswa.profile', compact('user'));
    }

    /**
     * Update authenticated user's basic profile data (name, phone, email)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['required','email','max:255','unique:users,email,' . $user->id . ',id'],
        ]);

        // Update or create profile row
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
        if (array_key_exists('full_name', $validated)) {
            $profile->full_name = $validated['full_name'];
        }
        if (array_key_exists('phone', $validated)) {
            $profile->phone = $validated['phone'];
        }
        $profile->save();

        // Email is stored on users table
        $user->email = $validated['email'];
        $user->save();

        $user->load(['profile']);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user,
        ]);
    }

    /**
     * Update authenticated student's profile data including NIM and Study Program
     */
    public function updateStudentProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name' => ['nullable','string','max:255'],
            'nim' => ['nullable','string','max:100'],
            'study_program' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['required','email','max:255','unique:users,email,' . $user->id . ',id'],
        ]);

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
        if (array_key_exists('full_name', $validated)) {
            $profile->full_name = $validated['full_name'];
        }
        if (array_key_exists('nim', $validated)) {
            $profile->nim = $validated['nim'];
        }
        if (array_key_exists('study_program', $validated)) {
            // Kolom pada database adalah program_studi (bukan study_program)
            $profile->program_studi = $validated['study_program'];
        }
        if (array_key_exists('phone', $validated)) {
            $profile->phone = $validated['phone'];
        }
        $profile->save();

        $user->email = $validated['email'];
        $user->save();

        $user->load(['profile']);

        return response()->json([
            'message' => 'Profil mahasiswa berhasil diperbarui',
            'data' => $user,
        ]);
    }

    /**
     * Update profile photo immediately on file change
     */
    public function updateProfilePhoto(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'photo' => ['required','image','mimes:jpg,jpeg,png,webp','max:2048'],
            ]);

            $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

            // Delete old file if stored locally under storage path
            if (!empty($profile->photo_url)) {
                // If stored as full URL like /storage/avatars/xxx
                $prefix = url('/storage') . '/';
                if (str_starts_with($profile->photo_url, $prefix)) {
                    $relative = substr($profile->photo_url, strlen($prefix));
                    if ($relative && Storage::disk('public')->exists($relative)) {
                        Storage::disk('public')->delete($relative);
                    }
                }
            }

            $path = $request->file('photo')->store('avatars', 'public');
            $publicUrl = Storage::url($path); // returns /storage/avatars/...
            $profile->photo_url = $publicUrl;
            $profile->save();

            $user->load('profile');

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui',
                'data' => $user,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to upload profile photo', [
                'user_id' => optional(Auth::user())->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Gagal upload foto',
                // Return as validation-like structure so UI can surface it under the photo field
                'errors' => [
                    'photo' => [config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan saat mengunggah foto.'],
                ],
            ], 500);
        }
    }

    /**
     * Change password with current password verification
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'old_password' => ['required','string'],
            'new_password' => ['required','string','min:8','confirmed'],
        ]);

        if (!Hash::check($validated['old_password'], $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->password = $validated['new_password'];
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diubah',
        ]);
    }
}
