<?php

use Illuminate\Support\Facades\Route;
use Modules\KnowledgeManager\Http\Controllers\AnswerController;
use Modules\KnowledgeManager\Http\Controllers\AssetController;
use Modules\KnowledgeManager\Http\Controllers\DashboardController;
use Modules\KnowledgeManager\Http\Controllers\DocumentController;
use Modules\KnowledgeManager\Http\Controllers\ProjectController;
use Modules\KnowledgeManager\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:knowledge-manager', EnsureCurrentTeam::class])
    ->prefix('app/knowledge')
    ->name('knowledgemanager.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('projects', ProjectController::class)->names('projects');

        Route::get('/projects/{project}/answers', [AnswerController::class, 'edit'])->name('answers.edit');
        Route::put('/projects/{project}/answers', [AnswerController::class, 'update'])->name('answers.update');

        Route::get('/projects/{project}/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::post('/projects/{project}/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::delete('/projects/{project}/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

        Route::get('/projects/{project}/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/projects/{project}/documents/{type}', [DocumentController::class, 'show'])->name('documents.show');
    });
