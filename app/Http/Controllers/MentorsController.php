<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profiles;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MentorsController extends Controller
{
    /**
     * Display mentors list page.
     */
    public function index()
    {
        $mentors = User::with(['profile'])
            ->where('role', 'pembina')
            ->orderBy('email')
            ->get();
        return view('admin_company.manage-mentors', compact('mentors'));
    }

    /**
     * Show a single mentor.
     */
    public function show(string $id)
    {
        $user = User::with(['profile'])->where('role', 'pembina')->findOrFail($id);
        return response()->json(['data' => $user]);
    }

    /**
     * Store a new mentor (user + profile).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $user = new User();
        $user->role = 'pembina';
        $user->email = $payload['email'];
        $user->is_active = $payload['is_active'] ?? true;
        $user->password = Hash::make($payload['password'] ?? 'Qwert123*');
        $user->save();

        $profile = new Profiles();
        $profile->user_id = $user->id;
        $profile->full_name = $payload['full_name'];
        $profile->phone = $payload['phone'] ?? null;
        $profile->whatsapp = $payload['whatsapp'] ?? null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/profiles');
            $profile->photo_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile']);

        return response()->json([
            'message' => 'Mentor created successfully',
            'data' => $user,
        ]);
    }

    /**
     * Update an existing mentor.
     */
    public function update(Request $request, string $id)
    {
        $user = User::where('role', 'pembina')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $user->id . ',id',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $user->email = $payload['email'];
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
        if ($request->boolean('photo_remove')) {
            if (!empty($profile->photo_url) && str_starts_with($profile->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $profile->photo_url = null;
        }
        if ($request->hasFile('photo')) {
            if (!empty($profile->photo_url) && str_starts_with($profile->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $path = $request->file('photo')->store('public/profiles');
            $profile->photo_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile']);

        return response()->json([
            'message' => 'Mentor updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a mentor.
     */
    public function destroy(string $id)
    {
        $user = User::where('role', 'pembina')->findOrFail($id);
        // delete profile first (and optionally the file)
        if ($user->profile) {
            $profile = $user->profile;
            if (!empty($profile->photo_url) && str_starts_with($profile->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $profile->delete();
        }
        $user->delete();

        return response()->json([
            'message' => 'Mentor deleted successfully',
        ]);
    }
}
