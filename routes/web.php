<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\InactivityController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PenaltyController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Inactivity tracking routes
    Route::post('/api/inactivity/penalty', [InactivityController::class, 'storePenalty'])->name('inactivity.penalty');
    Route::post('/api/activity/ping', [ActivityController::class, 'ping'])->name('activity.ping');
    
    // File upload/download routes
    Route::post('/upload', [UserController::class, 'uploadFile'])->name('file.upload');
    Route::get('/download/{id}', [UserController::class, 'downloadFile'])->name('file.download');
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // User management routes
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    
    // Activity logs routes
    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::post('/admin/activity-logs/filter', [ActivityLogController::class, 'filter'])->name('admin.activity-logs.filter');
    Route::delete('/admin/activity-logs/{activityLog}', [ActivityLogController::class, 'destroy'])->name('admin.activity-logs.destroy');
    
    // Penalties routes
    Route::get('/admin/penalties', [PenaltyController::class, 'index'])->name('admin.penalties.index');
    Route::put('/admin/penalties/{penalty}/deactivate', [PenaltyController::class, 'deactivate'])->name('admin.penalties.deactivate');
    Route::put('/admin/users/{user}/clear-penalties', [PenaltyController::class, 'clearUserPenalties'])->name('admin.penalties.clear-user');
    
    // Settings routes
    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingsController::class, 'store'])->name('admin.settings.store');
    Route::put('/admin/settings/{setting}', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::delete('/admin/settings/{setting}', [SettingsController::class, 'destroy'])->name('admin.settings.destroy');
    Route::get('/admin/settings/timeout', [SettingsController::class, 'getIdleTimeout'])->name('admin.settings.timeout');
    Route::get('/admin/settings/monitoring', [SettingsController::class, 'getMonitoringStatus'])->name('admin.settings.monitoring');
});

require __DIR__.'/auth.php';
