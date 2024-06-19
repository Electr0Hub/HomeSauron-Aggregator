<?php

use App\Http\Controllers\CameraController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::group(['prefix' => 'cameras', 'as' => 'cameras.'], function () {
    Route::get('/create', [CameraController::class, 'create'])->name('create');
    Route::get('/index', [CameraController::class, 'index'])->name('index');
    Route::get('/discover', [CameraController::class, 'discover'])->name('discover');
    Route::post('/store', [CameraController::class, 'store'])->name('store');
    Route::get('/{camera}', [CameraController::class, 'show'])->name('show');
    Route::delete('/{camera}', [CameraController::class, 'delete'])->name('delete');
});
