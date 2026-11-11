<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DailyWorkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkTypeController;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware(['auth', PreventBackHistory::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('contracts', ContractController::class);
    Route::resource('work-types', WorkTypeController::class)->except(['show', 'destroy']);
    Route::resource('daily-works', DailyWorkController::class);
    Route::post('/daily-works/photos/{photo}/delete', [DailyWorkController::class, 'deletePhoto'])->name('daily-works.delete-photo');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
});
