<?php

use App\Http\Controllers\Api\PrisonerController;
use App\Http\Controllers\Api\VisitationScheduleController;
use App\Http\Controllers\Api\AuthController;

Route::get('/prisoners', [PrisonerController::class, 'index']);


Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'role:CUSTOMER'])->group(function () {
    Route::post('/visitation-schedule', [VisitationScheduleController::class, 'store']);
    Route::get('/user/me', [AuthController::class, 'info']);
    Route::get('/visitation-schedules', [VisitationScheduleController::class, 'list']);
    Route::get('/visit-nearest', [VisitationScheduleController::class, 'nearest']);

});

Route::middleware(['auth:sanctum', 'role:GATE'])->group(function () {
    Route::get('/visitation-schedule/verify/{token}', [VisitationScheduleController::class, 'verify']);
});