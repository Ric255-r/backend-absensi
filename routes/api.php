<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\HomepageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::get('/me', 'me');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::post('/updateProfile', 'updateProfile');
});

Route::controller(DosenController::class)->group(function() {
    Route::get('/matkul', 'index');
    Route::get('/list_absen_mhs', 'listabsen');
    Route::post('/acc_absen', 'accabsen');
    Route::post('/acc_semua', 'accsemua');
    Route::post('/tolak_absen','tolakabsen');
});

Route::controller(HomepageController::class)->group(function() {
    Route::get('/list_matkul/{kode_matkul?}', 'dashboard');
    // Route::get('/rekapan_absen/{kode_matkul}', 'rekapanAbsen');
    Route::get('/all_matkul', 'getAllMatkul');
    Route::post('/regis_matkul', 'regisMatkul');
    Route::post('/record_absen', 'absen');
    Route::delete('/delete_matkul', 'deleteMatkul');
});

Route::post('/testapi', [HomepageController::class, 'testapi`']);