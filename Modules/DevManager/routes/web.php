<?php

use Illuminate\Support\Facades\Route;
use Modules\DevManager\Http\Controllers\BacklogController;
use Modules\DevManager\Http\Controllers\DashboardController;
use Modules\DevManager\Http\Controllers\IdeaController;
use Modules\DevManager\Http\Controllers\IntegrationController;
use Modules\DevManager\Http\Controllers\MilestoneController;
use Modules\DevManager\Http\Controllers\ReleaseController;
use Modules\DevManager\Http\Controllers\RequirementController;
use Modules\DevManager\Http\Controllers\RoadmapController;
use Modules\DevManager\Http\Controllers\UserStoryController;
use Modules\DevManager\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:dev-manager', EnsureCurrentTeam::class])
    ->prefix('app/dev')
    ->name('devmanager.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/backlog', [BacklogController::class, 'index'])->name('backlog.index');
        Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');

        Route::resource('ideas', IdeaController::class);

        Route::get('/ideas/{idea}/requirements', [RequirementController::class, 'index'])->name('requirements.index');
        Route::get('/ideas/{idea}/requirements/create', [RequirementController::class, 'create'])->name('requirements.create');
        Route::post('/ideas/{idea}/requirements', [RequirementController::class, 'store'])->name('requirements.store');
        Route::get('/requirements/{requirement}', [RequirementController::class, 'show'])->name('requirements.show');
        Route::get('/requirements/{requirement}/edit', [RequirementController::class, 'edit'])->name('requirements.edit');
        Route::put('/requirements/{requirement}', [RequirementController::class, 'update'])->name('requirements.update');
        Route::delete('/requirements/{requirement}', [RequirementController::class, 'destroy'])->name('requirements.destroy');

        Route::get('/ideas/{idea}/user-stories', [UserStoryController::class, 'index'])->name('user-stories.index');
        Route::get('/ideas/{idea}/user-stories/create', [UserStoryController::class, 'create'])->name('user-stories.create');
        Route::post('/ideas/{idea}/user-stories', [UserStoryController::class, 'store'])->name('user-stories.store');
        Route::get('/user-stories/{userStory}', [UserStoryController::class, 'show'])->name('user-stories.show');
        Route::get('/user-stories/{userStory}/edit', [UserStoryController::class, 'edit'])->name('user-stories.edit');
        Route::put('/user-stories/{userStory}', [UserStoryController::class, 'update'])->name('user-stories.update');
        Route::delete('/user-stories/{userStory}', [UserStoryController::class, 'destroy'])->name('user-stories.destroy');

        Route::get('/ideas/{idea}/milestones', [MilestoneController::class, 'index'])->name('milestones.index');
        Route::get('/ideas/{idea}/milestones/create', [MilestoneController::class, 'create'])->name('milestones.create');
        Route::post('/ideas/{idea}/milestones', [MilestoneController::class, 'store'])->name('milestones.store');
        Route::get('/milestones/{milestone}', [MilestoneController::class, 'show'])->name('milestones.show');
        Route::get('/milestones/{milestone}/edit', [MilestoneController::class, 'edit'])->name('milestones.edit');
        Route::put('/milestones/{milestone}', [MilestoneController::class, 'update'])->name('milestones.update');
        Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('milestones.destroy');

        Route::get('/ideas/{idea}/releases', [ReleaseController::class, 'index'])->name('releases.index');
        Route::get('/ideas/{idea}/releases/create', [ReleaseController::class, 'create'])->name('releases.create');
        Route::post('/ideas/{idea}/releases', [ReleaseController::class, 'store'])->name('releases.store');
        Route::get('/releases/{release}', [ReleaseController::class, 'show'])->name('releases.show');
        Route::get('/releases/{release}/edit', [ReleaseController::class, 'edit'])->name('releases.edit');
        Route::put('/releases/{release}', [ReleaseController::class, 'update'])->name('releases.update');
        Route::delete('/releases/{release}', [ReleaseController::class, 'destroy'])->name('releases.destroy');

        Route::resource('integrations', IntegrationController::class)->except(['create', 'edit', 'show']);
    });
