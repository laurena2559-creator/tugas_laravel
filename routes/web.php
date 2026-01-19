<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoanController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {

    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile');

    Route::middleware('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('category', CategoryController::class);

        // Item Routes
        Route::get('/item/{id}/qr', [ItemController::class, 'show'])->name('item.qr');
        Route::resource('item', ItemController::class);

        // Barang Rusak Routes - DIPERBAIKI
        Route::get('/item-rusak', [ItemController::class, 'rusak'])->name('item.rusak');
        Route::get('/item-perbaikan', [ItemController::class, 'perbaikan'])->name('item.perbaikan');
        Route::post('/item/{item}/repair', [ItemController::class, 'repair'])->name('item.repair');
        Route::post('/item/{item}/mark-damaged', [ItemController::class, 'markAsDamaged'])->name('item.mark.damaged');
        Route::get('/item-statistics', [ItemController::class, 'statistics'])->name('item.statistics');

        // Quick action untuk barang rusak - DIPERBAIKI
        Route::post('/item/{item}/add-damage', function ($itemId) {
            return redirect()->route('item.edit', $itemId)->with('show_damage_modal', true);
        })->name('item.quick.damage');

        Route::post('/item/{item}/quick-repair', function ($itemId) {
            return redirect()->route('item.perbaikan')->with('focus_item', $itemId);
        })->name('item.quick.repair');

        // Loan Routes
        Route::resource('loan', LoanController::class);
        Route::put('loan/{loan}/return', [LoanController::class, 'returnItem'])->name('loan.return');
        // Reactivate a returned loan (set status back to Dipinjam)
        Route::put('loan/{loan}/reactivate', [LoanController::class, 'reactivate'])->name('loan.reactivate');
        // Availability API for date-aware stock checks (used by loan create/edit JS)
        Route::get('/loan/availability', [LoanController::class, 'availability'])->name('loan.availability');
        Route::get('/loan/kembalikan/{id}', [LoanController::class, 'kembalikan'])->name('loan.kembalikan');
        //Report Pdf
        Route::get('/admin/loan/report', [LoanController::class, 'exportBulanan'])->name('loan.exportBulanan');
        // Monthly stock requirement report (HTML view)
        Route::get('/admin/loan/stock-monthly', [LoanController::class, 'stockMonthly'])->name('loan.stockMonthly');
        Route::get('/admin/loan/availability', [LoanController::class, 'checkAvailability'])->name('loan.availability');
    });
});

require __DIR__ . '/auth.php';