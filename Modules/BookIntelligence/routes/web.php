<?php

use Illuminate\Support\Facades\Route;
use Modules\BookIntelligence\Http\Controllers\AffiliateController;
use Modules\BookIntelligence\Http\Controllers\BookAnalysisController;
use Modules\BookIntelligence\Http\Controllers\BookController;
use Modules\BookIntelligence\Http\Controllers\ContentEngineController;
use Modules\BookIntelligence\Http\Controllers\DashboardController;
use Modules\BookIntelligence\Http\Controllers\GuideController;
use Modules\BookIntelligence\Http\Controllers\KnowledgeGapController;
use Modules\BookIntelligence\Http\Controllers\LibrarySetupController;
use Modules\BookIntelligence\Http\Controllers\QuestionMappingController;
use Modules\BookIntelligence\Http\Controllers\ReadingProgressController;
use Modules\BookIntelligence\Http\Controllers\RepurposingController;
use Modules\BookIntelligence\Http\Controllers\SearchController;
use Modules\BookIntelligence\Http\Middleware\EnsureCurrentTeam;

// Public affiliate link redirection
Route::get('/app/books/affiliate/redirect/{book}', [AffiliateController::class, 'redirect'])
    ->name('bookintelligence.affiliate.redirect');

Route::middleware(['auth', 'verified', 'module:book-intelligence', EnsureCurrentTeam::class])
    ->prefix('app/books')
    ->name('bookintelligence.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/guide', GuideController::class)->name('guide');

        // Module 1 & 2: Library & Analysis
        Route::resource('library', BookController::class)
            ->parameters(['library' => 'book'])
            ->names('books');
        Route::put('/library/{book}/reading-progress', [ReadingProgressController::class, 'update'])
            ->name('books.reading-progress');
        Route::post('/library/{book}/analysis', [BookAnalysisController::class, 'store'])
            ->name('books.analysis.store');
        Route::get('/library/{book}/analysis/status', [BookAnalysisController::class, 'status'])
            ->name('books.analysis.status');

        // Module 3: Question Mapping Engine & FAQ Database
        Route::get('/questions', [QuestionMappingController::class, 'index'])->name('questions.index');
        Route::get('/questions/create', [QuestionMappingController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionMappingController::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}/edit', [QuestionMappingController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [QuestionMappingController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionMappingController::class, 'destroy'])->name('questions.destroy');
        Route::post('/library/{book}/questions/generate', [QuestionMappingController::class, 'generate'])->name('questions.generate');

        // Module 4: Knowledge Search Engine
        Route::get('/search', [SearchController::class, 'index'])->name('search.index');

        // Module 5: Knowledge Gap Detection
        Route::get('/gaps', [KnowledgeGapController::class, 'index'])->name('gaps.index');
        Route::post('/gaps/{gap}/recommendations', [KnowledgeGapController::class, 'refreshRecommendations'])->name('gaps.recommendations');
        Route::post('/gaps/{gap}/import', [KnowledgeGapController::class, 'importBook'])->name('gaps.import');
        Route::patch('/gaps/{gap}/status', [KnowledgeGapController::class, 'updateStatus'])->name('gaps.status');

        // Module 6 & 7: AI Content & SEO Intelligence + 8-Section Blog Creation
        Route::get('/content', [ContentEngineController::class, 'index'])->name('content.index');
        Route::post('/content/discover', [ContentEngineController::class, 'discover'])->name('content.discover');
        Route::post('/content/blog/generate', [ContentEngineController::class, 'generateBlog'])->name('content.blog.generate');
        Route::get('/content/blog/{blog}', [ContentEngineController::class, 'showBlog'])->name('content.blog.show');
        Route::post('/content/blog/{blog}/publish', [ContentEngineController::class, 'publishBlog'])->name('content.blog.publish');

        // Module 8: Affiliate Monetization Engine
        Route::get('/affiliate', [AffiliateController::class, 'index'])->name('affiliate.index');

        // Module 9: Book-to-Content Repurposing Engine
        Route::get('/repurposing', [RepurposingController::class, 'index'])->name('repurposing.index');
        Route::get('/repurposing/{book}', [RepurposingController::class, 'show'])->name('repurposing.show');
        Route::post('/repurposing/{book}/generate', [RepurposingController::class, 'generate'])->name('repurposing.generate');

        // Setup
        Route::get('/setup', [LibrarySetupController::class, 'index'])->name('setup.index');
        Route::post('/setup/authors', [LibrarySetupController::class, 'storeAuthor'])->name('setup.authors.store');
        Route::delete('/setup/authors/{author}', [LibrarySetupController::class, 'destroyAuthor'])->name('setup.authors.destroy');
        Route::post('/setup/publishers', [LibrarySetupController::class, 'storePublisher'])->name('setup.publishers.store');
        Route::delete('/setup/publishers/{publisher}', [LibrarySetupController::class, 'destroyPublisher'])->name('setup.publishers.destroy');
        Route::post('/setup/topics', [LibrarySetupController::class, 'storeTopic'])->name('setup.topics.store');
        Route::delete('/setup/topics/{topic}', [LibrarySetupController::class, 'destroyTopic'])->name('setup.topics.destroy');
    });
