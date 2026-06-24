<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PrisonerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/admin', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

// Route::prefix('admin')
//     ->middleware(['auth'])
//     ->group(function () {

//         Route::get(
//             '/customers',
//             [CustomerController::class, 'index']
//         )->name('admin.customers.index');

//         Route::get(
//             '/customers/{customer}',
//             [CustomerController::class, 'show']
//         )->name('admin.customers.show');

//         Route::post(
//             '/customers/{customer}/toggle',
//             [CustomerController::class, 'toggle']
//         )->name('admin.customers.toggle');
//     });


Route::middleware(['web', 'auth:twill_users'])
    ->prefix('admin')
    ->group(function () {

        Route::get(
            '/thongke',
            [DashboardController::class, 'index']
        )->name('admin.thongke.index');

        Route::post(
            '/customers/{customer}/role',
            [CustomerController::class, 'changeRole']
        )->name('admin.customers.role');

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
