<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\MentorsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// routes for guest
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('login');
    })->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

// routes for user with auth
Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('dosen')->middleware('role:dosen')->group(function () {

});

Route::prefix('pembina')->middleware('role:pembina')->group(function () {

});

Route::prefix('mahasiswa')->middleware('role:mahasiswa')->group(function () {

});

// Company Admin routes
Route::prefix('company-admin')->middleware(['auth','role:company_admin'])->group(function () {
    // Campuses Management
    Route::get('/campuses', [CampusController::class, 'index'])->name('company_admin.campuses.index');
    Route::get('/campuses/{id}', [CampusController::class, 'show'])->name('company_admin.campuses.show');
    Route::post('/campuses', [CampusController::class, 'store'])->name('company_admin.campuses.store');
    Route::put('/campuses/{id}', [CampusController::class, 'update'])->name('company_admin.campuses.update');
    Route::delete('/campuses/{id}', [CampusController::class, 'destroy'])->name('company_admin.campuses.destroy');

    // Lecturers Management
    Route::get('/lecturers', [LecturerController::class, 'index'])->name('company_admin.lecturers.index');
    Route::get('/lecturers/{id}', [LecturerController::class, 'show'])->name('company_admin.lecturers.show');
    Route::post('/lecturers', [LecturerController::class, 'store'])->name('company_admin.lecturers.store');
    Route::put('/lecturers/{id}', [LecturerController::class, 'update'])->name('company_admin.lecturers.update');
    Route::delete('/lecturers/{id}', [LecturerController::class, 'destroy'])->name('company_admin.lecturers.destroy');

    // Mentors (Pembina) Management
    Route::get('/mentors', [MentorsController::class, 'index'])->name('company_admin.mentors.index');
    Route::get('/mentors/{id}', [MentorsController::class, 'show'])->name('company_admin.mentors.show');
    Route::post('/mentors', [MentorsController::class, 'store'])->name('company_admin.mentors.store');
    Route::put('/mentors/{id}', [MentorsController::class, 'update'])->name('company_admin.mentors.update');
    Route::delete('/mentors/{id}', [MentorsController::class, 'destroy'])->name('company_admin.mentors.destroy');
});
