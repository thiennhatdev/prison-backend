<?php

use App\Http\Controllers\Api\PrisonerController;
use App\Http\Controllers\Api\VisitationScheduleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PrisonRuleController;
use App\Http\Controllers\Api\SurveyController;

Route::get('/prisoners', [PrisonerController::class, 'index']);


Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'role:CUSTOMER'])->group(function () {
    Route::post('/visitation-schedule', [VisitationScheduleController::class, 'store']);
    Route::get('/user/me', [AuthController::class, 'info']);
    Route::get('/visitation-schedules', [VisitationScheduleController::class, 'list']);
    Route::get('/visit-nearest', [VisitationScheduleController::class, 'nearest']);
    Route::get('/posts', [PostController::class, 'list']);
    Route::get('/post/{id}', [PostController::class, 'detail']);
    Route::get('/prison-rules', [PrisonRuleController::class, 'list']);
    Route::get('/prison-rule/{id}', [PrisonRuleController::class, 'detail']);
    Route::get('/surveys', [SurveyController::class, 'list']);
    Route::get('/survey/{id}', [SurveyController::class, 'detail']);
    Route::post('/survey', [SurveyController::class, 'create']);
});

Route::middleware(['auth:sanctum', 'role:GATE'])->group(function () {
    Route::get('/visitation-schedule/verify/{token}', [VisitationScheduleController::class, 'verify']);
});