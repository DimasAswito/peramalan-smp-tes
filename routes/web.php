<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagementKendaraan;
use App\Http\Controllers\InputDataController;
use App\Http\Controllers\PeramalanSmaController;
use App\Http\Controllers\PeramalanTesController;
use App\Http\Controllers\PerbandinganController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
  Route::get('/', [DashboardController::class, 'index']);

  // Management Kendaraan
  Route::get('/management-kendaraan', [ManagementKendaraan::class, 'index'])->name('management_kendaraan.index');
  Route::post('/management-kendaraan', [ManagementKendaraan::class, 'store'])->name('management_kendaraan.store');
  Route::put('/management-kendaraan/{id}', [ManagementKendaraan::class, 'update'])->name('management_kendaraan.update');
  Route::delete('/management-kendaraan/{id}', [ManagementKendaraan::class, 'destroy'])->name('management_kendaraan.destroy');

  // Input Data
  Route::get('/input-data', [InputDataController::class, 'index'])->name('input_data.index');
  Route::post('/input-data', [InputDataController::class, 'store'])->name('input_data.store');
  Route::put('/input-data/{id}', [InputDataController::class, 'update'])->name('input_data.update');
  Route::delete('/input-data/{id}', [InputDataController::class, 'destroy'])->name('input_data.destroy');

  // Peramalan SMA
  Route::get('/peramalan-smp', [PeramalanSmaController::class, 'index'])->name('peramalan_sma.index');
  Route::post('/peramalan-smp', [PeramalanSmaController::class, 'process'])->name('peramalan_sma.process');
  Route::post('/peramalan-smp/store', [PeramalanSmaController::class, 'store'])->name('peramalan_sma.store');
  Route::delete('/peramalan-smp/{id}', [PeramalanSmaController::class, 'destroy'])->name('peramalan_sma.destroy');

  // Peramalan TES
  Route::get('/peramalan-tes', [PeramalanTesController::class, 'index'])->name('peramalan_tes.index');
  Route::post('/peramalan-tes', [PeramalanTesController::class, 'process'])->name('peramalan_tes.process');
  Route::post('/peramalan-tes/store', [PeramalanTesController::class, 'store'])->name('peramalan_tes.store');
  Route::delete('/peramalan-tes/{id}', [PeramalanTesController::class, 'destroy'])->name('peramalan_tes.destroy');

  // Perbandingan
  Route::get('/perbandingan', [PerbandinganController::class, 'index'])->name('perbandingan.index');
  Route::post('/perbandingan', [PerbandinganController::class, 'process'])->name('perbandingan.process');
  Route::post('/perbandingan/store', [PerbandinganController::class, 'store'])->name('perbandingan.store');
  Route::delete('/perbandingan/{id}', [PerbandinganController::class, 'destroy'])->name('perbandingan.destroy');
});
