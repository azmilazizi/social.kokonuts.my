<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAnalytics\Http\Controllers\AppAnalyticsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(["prefix" => "app"], function () {
    Route::group(["prefix" => "analytics"], function () {
        Route::get('/', [AppAnalyticsController::class, 'index'])->name('app.analytics.index');
        Route::get('{platform}/{id}', [AppAnalyticsController::class, 'show'])->name('app.analytics.show');
    });
});
