<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campuses;
use Illuminate\Support\Facades\Validator;

class CampusController extends Controller
{
    /**
     * Display a listing of the campuses.
     */
    public function index()
    {
        $campuses = Campuses::orderBy('nama_campus')->get();
        return view('admin_company.manage-campus', compact('campuses'));
    }

    /**
     * Show a single campus by id.
     */
    public function show(string $id)
    {
        $campus = Campuses::findOrFail($id);
        return response()->json([
            'data' => $campus,
        ]);
    }

    /**
     * Store a newly created campus.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'nama_campus' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email_campus' => 'required|email|max:255',
            'alamat_campus' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $campus = Campuses::create($validator->validated());

        return response()->json([
            'message' => 'Campus created successfully',
            'data' => $campus,
        ]);
    }

    /**
     * Update the specified campus.
     */
    public function update(Request $request, string $id)
    {
        $campus = Campuses::findOrFail($id);

        $data = $request->all();
        $validator = Validator::make($data, [
            'nama_campus' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email_campus' => 'required|email|max:255',
            'alamat_campus' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $campus->update($validator->validated());

        return response()->json([
            'message' => 'Campus updated successfully',
            'data' => $campus->refresh(),
        ]);
    }

    /**
     * Remove the specified campus.
     */
    public function destroy(string $id)
    {
        $campus = Campuses::findOrFail($id);
        $campus->delete();

        return response()->json([
            'message' => 'Campus deleted successfully',
        ]);
    }
}
