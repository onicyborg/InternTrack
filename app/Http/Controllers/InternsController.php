<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profiles;
use App\Models\Campuses;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InternsController extends Controller
{
    /**
     * List interns page
     */
    public function index()
    {
        $interns = User::with(['profile','campus','dosen.profile','pembina.profile'])
            ->where('role','mahasiswa')
            ->orderBy('email')
            ->get();
        $mentors = User::with('profile')->where('role','pembina')->orderBy('email')->get();
        $campuses = Campuses::orderBy('nama_campus')->get();
        return view('admin_company.manage-interns', compact('interns','mentors','campuses'));
    }

    /**
     * Show single intern
     */
    public function show(string $id)
    {
        $user = User::with(['profile','campus','dosen.profile','pembina.profile'])
            ->where('role','mahasiswa')
            ->findOrFail($id);
        return response()->json(['data'=>$user]);
    }

    /**
     * Create intern
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'pembina_user_id' => [
                'required','uuid',
                Rule::exists('users','id')->where(fn($q)=>$q->where('role','pembina')),
            ],
            'campus_id' => 'required|uuid|exists:campuses,id',
            'dosen_user_id' => [
                'required','uuid',
                Rule::exists('users','id')->where(function($q) use ($request){
                    $q->where('role','dosen');
                    if ($request->campus_id) { $q->where('campus_id',$request->campus_id); }
                }),
            ],
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'nim' => 'required|string|max:100',
            'program_studi' => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message'=>'Validation error','errors'=>$validator->errors()],422);
        }
        $payload = $validator->validated();

        $user = new User();
        $user->role = 'mahasiswa';
        $user->email = $payload['email'];
        $user->campus_id = $payload['campus_id'];
        $user->pembina_user_id = $payload['pembina_user_id'];
        $user->dosen_user_id = $payload['dosen_user_id'];
        $user->is_active = $payload['is_active'] ?? true;
        $user->password = Hash::make($payload['password'] ?? 'Qwert123*');
        $user->save();

        $profile = new Profiles();
        $profile->user_id = $user->id;
        $profile->full_name = $payload['full_name'];
        $profile->phone = $payload['phone'] ?? null;
        $profile->whatsapp = $payload['whatsapp'] ?? null;
        $profile->nim = $payload['nim'];
        $profile->program_studi = $payload['program_studi'];
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/profiles');
            $profile->photo_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile','campus','dosen.profile','pembina.profile']);
        return response()->json(['message'=>'Intern created successfully','data'=>$user]);
    }

    /**
     * Update intern
     */
    public function update(Request $request, string $id)
    {
        $user = User::where('role','mahasiswa')->findOrFail($id);
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,'.$user->id.',id',
            'pembina_user_id' => [
                'required','uuid',
                Rule::exists('users','id')->where(fn($q)=>$q->where('role','pembina')),
            ],
            'campus_id' => 'required|uuid|exists:campuses,id',
            'dosen_user_id' => [
                'required','uuid',
                Rule::exists('users','id')->where(function($q) use ($request){
                    $q->where('role','dosen');
                    if ($request->campus_id) { $q->where('campus_id',$request->campus_id); }
                }),
            ],
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'nim' => 'required|string|max:100',
            'program_studi' => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message'=>'Validation error','errors'=>$validator->errors()],422);
        }
        $payload = $validator->validated();

        $user->email = $payload['email'];
        $user->campus_id = $payload['campus_id'];
        $user->pembina_user_id = $payload['pembina_user_id'];
        $user->dosen_user_id = $payload['dosen_user_id'];
        if (array_key_exists('is_active',$payload)) { $user->is_active = $payload['is_active']; }
        if (!empty($payload['password'] ?? null)) { $user->password = Hash::make($payload['password']); }
        $user->save();

        $profile = $user->profile ?: new Profiles(['user_id'=>$user->id]);
        $profile->full_name = $payload['full_name'];
        $profile->phone = $payload['phone'] ?? null;
        $profile->whatsapp = $payload['whatsapp'] ?? null;
        $profile->nim = $payload['nim'];
        $profile->program_studi = $payload['program_studi'];
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

        $user->load(['profile','campus','dosen.profile','pembina.profile']);
        return response()->json(['message'=>'Intern updated successfully','data'=>$user]);
    }

    /**
     * Delete intern
     */
    public function destroy(string $id)
    {
        $user = User::where('role','mahasiswa')->findOrFail($id);
        if ($user->profile) {
            $profile = $user->profile;
            if (!empty($profile->photo_url) && str_starts_with($profile->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $profile->delete();
        }
        $user->delete();
        return response()->json(['message'=>'Intern deleted successfully']);
    }

    /**
     * Helper: list lecturers by campus for dependent dropdown
     */
    public function lecturersByCampus(string $campusId)
    {
        $lecturers = User::with('profile')
            ->where('role','dosen')
            ->where('campus_id',$campusId)
            ->orderBy('email')
            ->get(['id','email','campus_id']);
        return response()->json(['data'=>$lecturers]);
    }
}
