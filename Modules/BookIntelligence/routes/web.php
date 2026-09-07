<?php

use Illuminate\Support\Facades\Route;
use Modules\BookIntelligence\Http\Controllers\BookAnalysisController;
use Modules\BookIntelligence\Http\Controllers\BookController;
use Modules\BookIntelligence\Http\Controllers\DashboardController;
use Modules\BookIntelligence\Http\Controllers\GuideController;
use Modules\BookIntelligence\Http\Controllers\LibrarySetupController;
use Modules\BookIntelligence\Http\Controllers\ReadingProgressController;
use Modules\BookIntelligence\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:book-intelligence', EnsureCurrentTeam::class])
    ->prefix('app/books')
    ->name('bookintelligence.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/guide', GuideController::class)->name('guide');

        Route::resource('library', BookController::class)
            ->parameters(['library' => 'book'])
            ->names('books');
        Route::put('/library/{book}/reading-progress', [ReadingProgressController::class, 'update'])
            ->name('books.reading-progress');
        Route::post('/library/{book}/analysis', [BookAnalysisController::class, 'store'])
            ->name('books.analysis.store');
        Route::get('/library/{book}/analysis/status', [BookAnalysisController::class, 'status'])
            ->name('books.analysis.status');

        Route::get('/setup', [LibrarySetupController::class, 'index'])->name('setup.index');
        Route::post('/setup/authors', [LibrarySetupController::class, 'storeAuthor'])->name('setup.authors.store');
        Route::delete('/setup/authors/{author}', [LibrarySetupController::class, 'destroyAuthor'])->name('setup.authors.destroy');
        Route::post('/setup/publishers', [LibrarySetupController::class, 'storePublisher'])->name('setup.publishers.store');
        Route::delete('/setup/publishers/{publisher}', [LibrarySetupController::class, 'destroyPublisher'])->name('setup.publishers.destroy');
        Route::post('/setup/topics', [LibrarySetupController::class, 'storeTopic'])->name('setup.topics.store');
        Route::delete('/setup/topics/{topic}', [LibrarySetupController::class, 'destroyTopic'])->name('setup.topics.destroy');
    });
