<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientProjectsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\RefreshController;
use App\Http\Controllers\RenewalsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// --- Guest ---
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// --- Authenticated ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sales', [SalesController::class, 'index'])->name('sales');
    Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
    Route::get('/operations', [OperationsController::class, 'index'])->name('operations');
    Route::get('/client-projects', [ClientProjectsController::class, 'index'])->name('client-projects');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/security', [SecurityController::class, 'index'])->name('security');
    Route::get('/renewals', [RenewalsController::class, 'index'])->name('renewals');
    Route::post('/refresh-data', RefreshController::class)->name('refresh-data');
});

// Local-only: auto-login preview used for layout QA screenshots. 404s outside local.
if (app()->environment('local')) {
    Route::get('/__preview/{path}', function (string $path) {
        auth()->login(\App\Models\User::firstOrFail());
        return redirect($path === 'dashboard' ? '/' : '/' . ltrim($path, '/'));
    })->where('path', '.*');
}
