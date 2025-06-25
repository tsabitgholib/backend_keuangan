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

Route::get('/buku-besar', function () {
    return view('buku-besar');
});
Route::get('/neraca-saldo', function () {
    return view('neraca-saldo');
});
Route::get('/posisi-keuangan', function () {
    return view('posisi-keuangan');
});
Route::get('/aktivitas', function () {
    return view('aktivitas');
});
Route::get('/perbandingan-bulan', function () {
    return view('perbandingan-bulan');
});
