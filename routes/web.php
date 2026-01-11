<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagementKendaraan;
use App\Http\Controllers\InputDataController;
use App\Http\Controllers\PeramalanSmpController;
use App\Http\Controllers\PeramalanTesController;
use App\Http\Controllers\PerbandinganController;
use App\Http\Controllers\AuthController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/login', [AuthController::class, 'index']);
Route::get('/management-kendaraan', [ManagementKendaraan::class, 'index'])->name('management_kendaraan.index');
Route::post('/management-kendaraan', [ManagementKendaraan::class, 'store'])->name('management_kendaraan.store');
Route::put('/management-kendaraan/{id}', [ManagementKendaraan::class, 'update'])->name('management_kendaraan.update');
Route::delete('/management-kendaraan/{id}', [ManagementKendaraan::class, 'destroy'])->name('management_kendaraan.destroy');
Route::get('/input-data', [InputDataController::class, 'index'])->name('input_data.index');
Route::post('/input-data', [InputDataController::class, 'store'])->name('input_data.store');
Route::put('/input-data/{id}', [InputDataController::class, 'update'])->name('input_data.update');
Route::delete('/input-data/{id}', [InputDataController::class, 'destroy'])->name('input_data.destroy');
Route::get('/peramalan-smp', [PeramalanSmpController::class, 'index']);
Route::get('/peramalan-tes', [PeramalanTesController::class, 'index']);
Route::get('/perbandingan', [PerbandinganController::class, 'index']);
