<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PrisonerController;
use App\Http\Controllers\Admin\CustomerController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/prisoners', [PrisonerController::class, 'index']);
Route::prefix('admin')
    // ->middleware(['auth'])
    ->group(function () {

        Route::get(
            '/customers',
            [CustomerController::class, 'index']
        )->name('admin.customers.index');

        Route::get(
            '/customers/{customer}',
            [CustomerController::class, 'show']
        )->name('admin.customers.show');

        Route::post(
            '/customers/{customer}/toggle',
            [CustomerController::class, 'toggle']
        )->name('admin.customers.toggle');
    });
