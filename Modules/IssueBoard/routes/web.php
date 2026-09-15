<?php

use Illuminate\Support\Facades\Route;
use Modules\IssueBoard\Livewire\Board;
use Modules\IssueBoard\Livewire\IssueDetail;
use Modules\IssueBoard\Livewire\IssueForm;

Route::middleware(config('issueboard.route.middleware'))
    ->prefix(config('issueboard.route.prefix'))
    ->name('issueboard.')
    ->group(function () {
        Route::get('/', Board::class)->name('index');
        Route::get('/neu', IssueForm::class)->name('create');
        Route::get('/{issue}', IssueDetail::class)->name('show');
        Route::get('/{issue}/bearbeiten', IssueForm::class)->name('edit');
    });
