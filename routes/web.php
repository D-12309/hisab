<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\ExpensePartyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BankController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['mobile.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/opening-balances', [DashboardController::class, 'updateOpeningBalances'])->name('dashboard.update-opening-balances');

    // Renters CRUD, Payments & Refunds
    Route::resource('renters', RenterController::class);
    Route::post('renters/{renter}/payment', [RenterController::class, 'recordPayment'])->name('renters.record-payment');
    Route::post('renters/{renter}/refund', [RenterController::class, 'refundDeposit'])->name('renters.refund-deposit');

    // Expense Parties CRUD & Logging
    Route::resource('expense-parties', ExpensePartyController::class);
    Route::post('expense-parties/{expense_party}/expense', [ExpensePartyController::class, 'recordExpense'])->name('expense-parties.record-expense');

    // Transactions CRUD
    Route::resource('transactions', TransactionController::class);

    // Banks/Bankers CRUD
    Route::resource('banks', BankController::class);
});

