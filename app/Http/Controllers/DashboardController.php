<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Campuses;

class DashboardController extends Controller
{
    public function index()
    {
        if(Auth::user()->role == 'company_admin') {
            // Totals for mahasiswa
            $mahasiswaActive = User::where('role', 'mahasiswa')->where('is_active', 1)->count();
            $mahasiswaInactive = User::where('role', 'mahasiswa')->where('is_active', 0)->count();

            // Totals for dosen
            $dosenActive = User::where('role', 'dosen')->where('is_active', 1)->count();
            $dosenInactive = User::where('role', 'dosen')->where('is_active', 0)->count();

            // Totals for pembina
            $pembinaActive = User::where('role', 'pembina')->where('is_active', 1)->count();
            $pembinaInactive = User::where('role', 'pembina')->where('is_active', 0)->count();

            // Total campuses
            $campusTotal = Campuses::count();

            return view('admin_company.index', compact(
                'mahasiswaActive', 'mahasiswaInactive',
                'dosenActive', 'dosenInactive',
                'pembinaActive', 'pembinaInactive',
                'campusTotal'
            ));
        } elseif(Auth::user()->role == 'dosen') {
            return view('dosen.index');
        } elseif(Auth::user()->role == 'pembina') {
            return view('pembina.index');
        } elseif(Auth::user()->role == 'mahasiswa') {
            return view('mahasiswa.index');
        }
    }
}
