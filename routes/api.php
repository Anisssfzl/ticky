<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DaftarTugasController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Task routes
    Route::apiResource('tasks', DaftarTugasController::class);
    Route::patch('/tasks/{id}/complete', [DaftarTugasController::class, 'markComplete']);
    Route::patch('/tasks/{id}/incomplete', [DaftarTugasController::class, 'markIncomplete']);
    Route::get('/tasks/refresh-overdue', [DaftarTugasController::class, 'refreshOverdueStatus'])->middleware('auth:sanctum');
    Route::get('/tasks-statistics', [DaftarTugasController::class, 'statistics']);
    Route::get('/category-statistics', [DaftarTugasController::class, 'getKategoriStats']);
    Route::get('/tasks/export-pdf', [DaftarTugasController::class, 'exportToPdf']);

});