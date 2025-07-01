<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    // COA
    Route::get('coa', [App\Http\Controllers\COAController::class, 'index']);
    Route::get('coa/tree', [App\Http\Controllers\COAController::class, 'tree']);
    Route::post('coa/create', [App\Http\Controllers\COAController::class, 'store']);
    Route::get('coa/{id}', [App\Http\Controllers\COAController::class, 'show']);
    Route::put('coa/{id}', [App\Http\Controllers\COAController::class, 'update']);
    Route::delete('coa/{id}', [App\Http\Controllers\COAController::class, 'destroy']);

    // Periode
    Route::get('periode', [App\Http\Controllers\PeriodeController::class, 'index']);
    Route::post('periode', [App\Http\Controllers\PeriodeController::class, 'store']);
    Route::get('periode/{id}', [App\Http\Controllers\PeriodeController::class, 'show']);
    Route::put('periode/{id}', [App\Http\Controllers\PeriodeController::class, 'update']);
    Route::delete('periode/{id}', [App\Http\Controllers\PeriodeController::class, 'destroy']);
    Route::post('periode/{id}/tutup', [App\Http\Controllers\PeriodeController::class, 'tutup']);

    // Saldo Awal
    Route::get('saldo-awal', [App\Http\Controllers\SaldoAwalController::class, 'index']);
    Route::get('saldo-awal/laporan', [App\Http\Controllers\SaldoAwalController::class, 'laporan']);
    Route::post('saldo-awal', [App\Http\Controllers\SaldoAwalController::class, 'store']);
    Route::get('saldo-awal/{id}', [App\Http\Controllers\SaldoAwalController::class, 'show']);
    Route::put('saldo-awal/{id}', [App\Http\Controllers\SaldoAwalController::class, 'update']);
    Route::delete('saldo-awal/{id}', [App\Http\Controllers\SaldoAwalController::class, 'destroy']);
    Route::get('saldo-awal/laporan', [App\Http\Controllers\SaldoAwalController::class, 'laporan']);
    Route::post('saldo-awal/batch', [App\Http\Controllers\SaldoAwalController::class, 'storeMany']);

    // Jurnal
    Route::get('jurnal', [App\Http\Controllers\JurnalController::class, 'index']);
    Route::post('jurnal', [App\Http\Controllers\JurnalController::class, 'store']);
    Route::get('jurnal/{id}', [App\Http\Controllers\JurnalController::class, 'show']);
    Route::put('jurnal/{id}', [App\Http\Controllers\JurnalController::class, 'update']);
    Route::delete('jurnal/{id}', [App\Http\Controllers\JurnalController::class, 'destroy']);

    // Laporan
    Route::get('laporan/buku-besar', [App\Http\Controllers\LaporanController::class, 'bukuBesar']);
    Route::get('laporan/neraca-saldo', [App\Http\Controllers\LaporanController::class, 'neracaSaldo']);
    Route::get('laporan/posisi-keuangan', [App\Http\Controllers\LaporanController::class, 'posisiKeuangan']);
    Route::get('laporan/aktivitas', [App\Http\Controllers\LaporanController::class, 'aktivitas']);
    Route::get('laporan/perbandingan-periode', [App\Http\Controllers\LaporanController::class, 'perbandinganPeriode']);
});
