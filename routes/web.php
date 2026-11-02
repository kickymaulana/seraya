<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SsoController::class, 'login'])->name('login');
    Route::get('/sso/login', [SsoController::class, 'ssoLogin'])->name('sso.login');
    Route::get('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [SsoController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::resource('categories', CategoryController::class)->except(['show', 'destroy']);
        Route::resource('item-types', ItemTypeController::class)->except(['show', 'destroy']);
        Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);
        Route::resource('positions', PositionController::class)->except(['show', 'destroy']);
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
    });

    Route::get('/master', MasterDataController::class)->middleware('role:admin')->name('master.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/account', fn () => redirect()->route('profile.edit'))->name('account.index');
    Route::get('/handovers', [HandoverController::class, 'index'])->name('handovers.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/handovers/pending', [HandoverController::class, 'pending'])->name('handovers.pending');
    Route::get('/handovers/create', [HandoverController::class, 'create'])->name('handovers.create');
    Route::get('/handovers/receivers/search', [HandoverController::class, 'searchReceivers'])->name('handovers.receivers.search');
    Route::get('/handovers/items/search', [HandoverController::class, 'searchItemTypes'])->name('handovers.items.search');
    Route::get('/handovers/batch/create', [HandoverController::class, 'batchCreate'])->name('handovers.batch.create');
    Route::post('/handovers/batch', [HandoverController::class, 'batchStore'])->name('handovers.batch.store');
    Route::get('/handovers/{handover}/pdf', [HandoverController::class, 'downloadPdf'])->name('handovers.pdf');
    Route::get('/handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
    Route::post('/handovers', [HandoverController::class, 'store'])->name('handovers.store');
    Route::post('/handovers/{handover}/confirm-sender', [HandoverController::class, 'confirmSender'])->name('handovers.confirm-sender');
    Route::post('/handovers/{handover}/confirm-receiver', [HandoverController::class, 'confirmReceiver'])->name('handovers.confirm-receiver');
});
