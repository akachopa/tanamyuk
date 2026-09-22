<?php

use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AssessmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommodityController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\GardenController;
use App\Http\Controllers\Api\V1\HarvestController;
use App\Http\Controllers\Api\V1\IssueController;
use App\Http\Controllers\Api\V1\JournalController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\PlantingCycleController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

if (! function_exists('registerPlantingCycleRoutes')) {
    function registerPlantingCycleRoutes(string $prefix): void
    {
        Route::get($prefix, [PlantingCycleController::class, 'index']);
        Route::post($prefix, [PlantingCycleController::class, 'store']);
        Route::get($prefix.'/{plantingCycle}', [PlantingCycleController::class, 'show']);
        Route::match(['put', 'patch'], $prefix.'/{plantingCycle}', [PlantingCycleController::class, 'update']);
        Route::post($prefix.'/{plantingCycle}/activate', [PlantingCycleController::class, 'activate']);
        Route::post($prefix.'/{plantingCycle}/complete', [PlantingCycleController::class, 'complete']);
    }
}

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('assessments/questions', [AssessmentController::class, 'questions']);
    Route::get('assessment/questions', [AssessmentController::class, 'questions']);
    Route::post('assessments', [AssessmentController::class, 'store']);
    Route::get('assessments/{assessment}/recommendations', [AssessmentController::class, 'recommendations']);

    Route::get('commodities', [CommodityController::class, 'index']);
    Route::get('commodities/{slug}', [CommodityController::class, 'show']);

    Route::get('articles/faqs', [ArticleController::class, 'faqs']);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);

    Route::get('plans', [PlanController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::match(['put', 'patch'], 'auth/me', [AuthController::class, 'updateMe']);

        Route::get('subscription', [PlanController::class, 'subscription']);

        Route::get('gardens', [GardenController::class, 'index']);
        Route::post('gardens', [GardenController::class, 'store']);
        Route::get('gardens/{garden}', [GardenController::class, 'show']);
        Route::match(['put', 'patch'], 'gardens/{garden}', [GardenController::class, 'update']);
        Route::delete('gardens/{garden}', [GardenController::class, 'destroy']);

        registerPlantingCycleRoutes('cycles');
        registerPlantingCycleRoutes('planting-cycles');

        Route::get('tasks', [TaskController::class, 'index']);
        Route::post('tasks', [TaskController::class, 'store']);
        Route::get('tasks/{task}', [TaskController::class, 'show']);
        Route::match(['put', 'patch'], 'tasks/{task}', [TaskController::class, 'update']);
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
        Route::post('tasks/{task}/postpone', [TaskController::class, 'postpone']);
        Route::post('tasks/{task}/reopen', [TaskController::class, 'reopen']);

        Route::get('journals', [JournalController::class, 'index']);
        Route::post('journals', [JournalController::class, 'store']);
        Route::match(['put', 'patch'], 'journals/{journal}', [JournalController::class, 'update']);

        Route::get('issues', [IssueController::class, 'index']);
        Route::post('issues', [IssueController::class, 'store']);
        Route::match(['put', 'patch'], 'issues/{issue}', [IssueController::class, 'update']);

        Route::get('expenses', [ExpenseController::class, 'index']);
        Route::post('expenses', [ExpenseController::class, 'store']);
        Route::match(['put', 'patch'], 'expenses/{expense}', [ExpenseController::class, 'update']);

        Route::get('harvests', [HarvestController::class, 'index']);
        Route::post('harvests', [HarvestController::class, 'store']);
        Route::match(['put', 'patch'], 'harvests/{harvest}', [HarvestController::class, 'update']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{orderNumber}', [OrderController::class, 'show']);
        Route::get('orders/{orderNumber}/status', [OrderController::class, 'checkStatus']);

        Route::get('dashboard', [DashboardController::class, 'home']);

        Route::post('sync/push', [SyncController::class, 'push']);
        Route::get('sync/pull', [SyncController::class, 'pull']);
    });
});
