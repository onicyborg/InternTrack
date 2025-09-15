<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\MentorsController;
use App\Http\Controllers\InternsController;
use App\Http\Controllers\LogbookController;
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

Route::prefix('mahasiswa')->middleware(['auth','role:mahasiswa'])->group(function () {
    // Absensi (check-in & check-out)
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('mahasiswa.attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('mahasiswa.attendance.checkout');
    // Attendance history page and data
    Route::get('/attendance', [AttendanceController::class, 'history'])->name('mahasiswa.attendance.index');
    Route::get('/attendance/data', [AttendanceController::class, 'historyData'])->name('mahasiswa.attendance.data');
    // Logbooks
    Route::get('/logbooks', [LogbookController::class, 'index'])->name('mahasiswa.logbooks.index');
    Route::post('/logbooks', [LogbookController::class, 'store'])->name('mahasiswa.logbooks.store');
    Route::get('/logbooks/{id}', [LogbookController::class, 'show'])->name('mahasiswa.logbooks.show');
    Route::put('/logbooks/{id}', [LogbookController::class, 'update'])->name('mahasiswa.logbooks.update');
    Route::delete('/logbooks/{id}', [LogbookController::class, 'destroy'])->name('mahasiswa.logbooks.destroy');
    // Profile
    Route::get('/profile', [AuthController::class, 'studentProfile'])->name('mahasiswa.profile');
    Route::put('/profile', [AuthController::class, 'updateStudentProfile'])->name('mahasiswa.profile.update');
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto'])->name('mahasiswa.profile.photo');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('mahasiswa.profile.change_password');
});

// Company Admin routes
Route::prefix('company-admin')->middleware(['auth','role:company_admin'])->group(function () {
    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('company_admin.profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('company_admin.profile.update');
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto'])->name('company_admin.profile.photo');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('company_admin.profile.change_password');

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

    // Interns (Mahasiswa) Management
    Route::get('/interns', [InternsController::class, 'index'])->name('company_admin.interns.index');
    Route::get('/interns/{id}', [InternsController::class, 'show'])->name('company_admin.interns.show');
    Route::post('/interns', [InternsController::class, 'store'])->name('company_admin.interns.store');
    Route::put('/interns/{id}', [InternsController::class, 'update'])->name('company_admin.interns.update');
    Route::delete('/interns/{id}', [InternsController::class, 'destroy'])->name('company_admin.interns.destroy');
    // Helper: get lecturers by campus for dependent dropdown
    Route::get('/campuses/{campusId}/lecturers', [InternsController::class, 'lecturersByCampus'])->name('company_admin.interns.lecturers_by_campus');
});
