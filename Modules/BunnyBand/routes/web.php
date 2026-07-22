<?php

use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;
use Modules\BunnyBand\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Modules\BunnyBand\Http\Controllers\Admin\DepositController;
use Modules\BunnyBand\Http\Controllers\Admin\DepositMethodController;
use Modules\BunnyBand\Http\Controllers\Admin\LevelController as AdminLevelController;
use Modules\BunnyBand\Http\Controllers\Admin\SettingController;
use Modules\BunnyBand\Http\Controllers\Admin\TaskController as AdminTaskController;
use Modules\BunnyBand\Http\Controllers\Admin\UserController;
use Modules\BunnyBand\Http\Controllers\Admin\WithdrawalController;
use Modules\BunnyBand\Http\Controllers\Admin\WithdrawalMethodController;
use Modules\BunnyBand\Http\Controllers\DashboardController;
use Modules\BunnyBand\Http\Controllers\LevelController;
use Modules\BunnyBand\Http\Controllers\ReferralController;
use Modules\BunnyBand\Http\Controllers\TaskController;
use Modules\BunnyBand\Http\Controllers\WalletController;
use Modules\BunnyBand\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:bunny-band', EnsureCurrentTeam::class])
    ->prefix('app/bunnyband')
    ->name('bunnyband.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');

        Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
        Route::post('wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
        Route::post('wallet/payment-method', [WalletController::class, 'savePaymentMethod'])->name('wallet.payment-method');

        Route::get('referrals', [ReferralController::class, 'index'])->name('referrals.index');
        Route::post('referrals/claim', [ReferralController::class, 'claim'])->name('referrals.claim');

        Route::get('levels', [LevelController::class, 'index'])->name('levels.index');
        Route::post('levels/{level}/upgrade', [LevelController::class, 'upgrade'])->name('levels.upgrade');
    });

Route::middleware(['auth', 'verified', 'module:bunny-band', EnsureCurrentTeam::class, EnsureAdmin::class])
    ->prefix('app/bunnyband/admin')
    ->name('bunnyband.admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{profile}', [UserController::class, 'show'])->name('users.show');
        Route::put('users/{profile}/block', [UserController::class, 'block'])->name('users.block');
        Route::post('users/{profile}/balance', [UserController::class, 'adjustBalance'])->name('users.balance');

        Route::get('tasks', [AdminTaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/create', [AdminTaskController::class, 'create'])->name('tasks.create');
        Route::post('tasks', [AdminTaskController::class, 'store'])->name('tasks.store');
        Route::get('tasks/{task}/edit', [AdminTaskController::class, 'edit'])->name('tasks.edit');
        Route::put('tasks/{task}', [AdminTaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{task}', [AdminTaskController::class, 'destroy'])->name('tasks.destroy');
        Route::get('tasks/submissions', [AdminTaskController::class, 'submissions'])->name('tasks.submissions');
        Route::post('tasks/submissions/{userTask}', [AdminTaskController::class, 'verify'])->name('tasks.submissions.verify');

        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('withdrawals/{transaction}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('withdrawals/{transaction}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::post('withdrawals/{transaction}/complete', [WithdrawalController::class, 'complete'])->name('withdrawals.complete');

        Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
        Route::post('deposits/{transaction}/approve', [DepositController::class, 'approve'])->name('deposits.approve');
        Route::post('deposits/{transaction}/reject', [DepositController::class, 'reject'])->name('deposits.reject');

        Route::get('levels', [AdminLevelController::class, 'index'])->name('levels.index');
        Route::get('levels/create', [AdminLevelController::class, 'create'])->name('levels.create');
        Route::post('levels', [AdminLevelController::class, 'store'])->name('levels.store');
        Route::get('levels/{level}/edit', [AdminLevelController::class, 'edit'])->name('levels.edit');
        Route::put('levels/{level}', [AdminLevelController::class, 'update'])->name('levels.update');
        Route::delete('levels/{level}', [AdminLevelController::class, 'destroy'])->name('levels.destroy');

        Route::get('withdrawal-methods', [WithdrawalMethodController::class, 'index'])->name('withdrawal-methods.index');
        Route::get('withdrawal-methods/create', [WithdrawalMethodController::class, 'create'])->name('withdrawal-methods.create');
        Route::post('withdrawal-methods', [WithdrawalMethodController::class, 'store'])->name('withdrawal-methods.store');
        Route::get('withdrawal-methods/{withdrawalMethod}/edit', [WithdrawalMethodController::class, 'edit'])->name('withdrawal-methods.edit');
        Route::put('withdrawal-methods/{withdrawalMethod}', [WithdrawalMethodController::class, 'update'])->name('withdrawal-methods.update');
        Route::delete('withdrawal-methods/{withdrawalMethod}', [WithdrawalMethodController::class, 'destroy'])->name('withdrawal-methods.destroy');

        Route::get('deposit-methods', [DepositMethodController::class, 'index'])->name('deposit-methods.index');
        Route::get('deposit-methods/create', [DepositMethodController::class, 'create'])->name('deposit-methods.create');
        Route::post('deposit-methods', [DepositMethodController::class, 'store'])->name('deposit-methods.store');
        Route::get('deposit-methods/{depositMethod}/edit', [DepositMethodController::class, 'edit'])->name('deposit-methods.edit');
        Route::put('deposit-methods/{depositMethod}', [DepositMethodController::class, 'update'])->name('deposit-methods.update');
        Route::delete('deposit-methods/{depositMethod}', [DepositMethodController::class, 'destroy'])->name('deposit-methods.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
