<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ManagerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/healthz', fn() => response('ok', 200));

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [LeaveRequestController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Leave request routes
    Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    Route::delete('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('leave-requests.destroy');
    
    // HR specific routes
    Route::get('api/users', [LeaveRequestController::class, 'getUsers'])->name('api.users');
    
    // Legacy manager routes (keeping for compatibility)
    Route::get('manager/dashboard', [ManagerController::class, 'index'])->name('manager.dashboard');
    Route::post('manager/approve/{leaveRequest}', [ManagerController::class, 'approve'])->name('manager.approve');
    Route::post('manager/reject/{leaveRequest}', [ManagerController::class, 'reject'])->name('manager.reject');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
