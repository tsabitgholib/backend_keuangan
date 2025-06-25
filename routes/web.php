<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanController;

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

Route::get('/buku-besar', [LaporanController::class, 'bukuBesarWeb']);
Route::get('/neraca-saldo', [LaporanController::class, 'neracaSaldoWeb']);
Route::get('/posisi-keuangan', [LaporanController::class, 'posisiKeuanganWeb']);
Route::get('/aktivitas', [LaporanController::class, 'aktivitasWeb']);
Route::get('/perbandingan-bulan', [LaporanController::class, 'perbandinganBulanWeb']);
Route::get('/login', function () {
    return view('login');
});
