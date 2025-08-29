<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->middleware('role:admin')->group(function () {

});

Route::prefix('dosen')->middleware('role:dosen')->group(function () {

});

Route::prefix('pembina')->middleware('role:pembina')->group(function (){

});

Route::prefix('mahasiswa')->middleware('role:mahasiswa')->group(function (){

});
