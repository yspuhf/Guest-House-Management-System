<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenditureHeadController;
use App\Http\Controllers\GuestHouseController;
use App\Http\Controllers\GuestTypeTariffController;
use App\Http\Controllers\NotificationLogController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Public tracking and authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::get('/track', [BookingController::class, 'track'])->name('bookings.track');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Bookings
    Route::resource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/submit', [BookingController::class, 'submitDraft'])->name('bookings.submit');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/{booking}/print', [BookingController::class, 'printVoucher'])->name('bookings.print');

    // Workflow
    Route::get('/workflow/pending', [WorkflowController::class, 'pendingQueue'])->name('workflow.pending');
    Route::post('/workflow/action/{booking}', [WorkflowController::class, 'handleAction'])->name('workflow.action');

    // Room Allocation
    Route::get('/allocations', [AllocationController::class, 'index'])->name('allocations.index');
    Route::get('/allocations/{booking}/allocate', [AllocationController::class, 'showAllocationForm'])->name('allocations.form');
    Route::post('/allocations/{booking}/allocate', [AllocationController::class, 'storeAllocation'])->name('allocations.store');
    Route::get('/calendar', [AllocationController::class, 'calendar'])->name('allocations.calendar');

    // Stay Operations
    Route::get('/operations/arrivals', [OperationsController::class, 'arrivals'])->name('operations.arrivals');
    Route::get('/operations/active-stays', [OperationsController::class, 'activeStays'])->name('operations.active_stays');
    Route::post('/operations/checkin/{booking}', [OperationsController::class, 'processCheckIn'])->name('operations.checkin');
    Route::post('/operations/checkout/{booking}', [OperationsController::class, 'processCheckOut'])->name('operations.checkout');
    Route::post('/operations/extensions/{booking}', [OperationsController::class, 'requestExtension'])->name('operations.extension.request');
    Route::post('/operations/extensions/{extension}/decide', [OperationsController::class, 'decideExtension'])->name('operations.extension.decide');
    Route::get('/operations/room-blocks', [OperationsController::class, 'manageBlocks'])->name('operations.blocks');
    Route::post('/operations/room-blocks', [OperationsController::class, 'storeBlock'])->name('operations.blocks.store');
    Route::post('/operations/room-blocks/{block}/remove', [OperationsController::class, 'removeBlock'])->name('operations.blocks.remove');

    // Billing & Finance
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/invoice/{invoice}', [BillingController::class, 'show'])->name('billing.invoice');
    Route::post('/billing/invoice/{invoice}/payment', [BillingController::class, 'recordPayment'])->name('billing.payment');
    Route::get('/billing/receipt/{payment}', [BillingController::class, 'showReceipt'])->name('billing.receipt');
    Route::post('/billing/invoice/{invoice}/refund', [BillingController::class, 'refund'])->name('billing.refund');

    // Reports
    Route::get('/reports/bookings', [ReportController::class, 'bookingsReport'])->name('reports.bookings');
    Route::get('/reports/availability', [ReportController::class, 'availabilityReport'])->name('reports.availability');
    Route::get('/reports/financial', [ReportController::class, 'financialReport'])->name('reports.financial');

    // Documents (Secure authenticated download)
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Configuration Masters (Admins)
    Route::prefix('masters')->name('masters.')->group(function () {
        Route::resource('guesthouses', GuestHouseController::class);
        Route::post('guesthouses/{guesthouse}/toggle-publish', [GuestHouseController::class, 'togglePublish'])->name('guesthouses.toggle_publish');

        Route::resource('roomtypes', RoomTypeController::class)->except(['create', 'show', 'edit']);
        Route::resource('rooms', RoomController::class);
        Route::post('rooms/{room}/toggle-maintenance', [RoomController::class, 'toggleMaintenance'])->name('rooms.toggle_maintenance');

        Route::get('tariffs', [GuestTypeTariffController::class, 'index'])->name('tariffs.index');
        Route::post('tariffs', [GuestTypeTariffController::class, 'updateTariffMatrix'])->name('tariffs.update');

        Route::resource('taxes', TaxController::class)->only(['index', 'store', 'update']);
        Route::resource('expenditure-heads', ExpenditureHeadController::class)->only(['index', 'store', 'update']);

        Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SystemSettingController::class, 'update'])->name('settings.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle_active');
    });

    // Oversight (Audit & Notifications)
    Route::get('/oversight/audit-logs', [AuditLogController::class, 'index'])->name('oversight.audit');
    Route::get('/oversight/notifications', [NotificationLogController::class, 'index'])->name('oversight.notifications');
});

