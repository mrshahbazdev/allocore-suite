<?php

use App\Http\Controllers\Api\McpController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ModuleResourceController;
use App\Http\Controllers\Api\V1\ModuleStatsController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api-token')->group(function () {
    Route::get('/user', UserController::class)->name('api.user');

    Route::get('/dashboard', DashboardController::class)->name('api.dashboard');
    Route::get('/modules', [ModuleStatsController::class, 'index'])->name('api.modules.index');
    Route::get('/modules/{module}', [ModuleStatsController::class, 'show'])->name('api.modules.show');
    Route::get('/modules/{module}/records', [ModuleResourceController::class, 'index'])->name('api.modules.records.index');
    Route::get('/modules/{module}/records/{id}', [ModuleResourceController::class, 'show'])->name('api.modules.records.show');
});

// Allocore Model Context Protocol (MCP) Endpoints (Open discovery + Bearer / Query Token authentication)
Route::match(['GET', 'POST', 'OPTIONS'], '/mcp', [McpController::class, 'handle'])->name('api.mcp');
Route::match(['GET', 'POST', 'OPTIONS'], '/mcp/sse', [McpController::class, 'handle'])->name('api.mcp.sse');
Route::match(['GET', 'POST', 'OPTIONS'], '/sse', [McpController::class, 'handle'])->name('api.sse');
Route::match(['GET', 'POST', 'OPTIONS'], '/mcp/rpc', [McpController::class, 'handleRpc'])->name('api.mcp.rpc');
Route::match(['GET', 'OPTIONS'], '/mcp/tools', [McpController::class, 'httpListTools'])->name('api.mcp.tools');
Route::match(['GET', 'OPTIONS'], '/mcp/resources', [McpController::class, 'httpListResources'])->name('api.mcp.resources');
Route::match(['GET', 'OPTIONS'], '/mcp/prompts', [McpController::class, 'httpListPrompts'])->name('api.mcp.prompts');



