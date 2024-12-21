<?php
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JWTVerify;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\isOwnerTaskMiddleware;

Route::get('/encrypt', [EncryptionController::class, 'encryptData']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware([JWTVerify::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

     Route::get('/tasks', [TaskController::class, 'index'])->middleware([RoleMiddleware::class . ':admin']);
     Route::post('/tasks', [TaskController::class, 'store']);
     Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware([isOwnerTaskMiddleware::class]);
        
});
