<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelYoutubeProfiles\Http\Controllers\AppChannelYoutubeProfilesController;

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

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "app"], function () {
        Route::group(["prefix" => "youtube"], function () {
            Route::group(["prefix" => "channel"], function () {
                Route::resource('/', AppChannelYoutubeProfilesController::class)->names('app.channelyoutubeprofiles');
                Route::get('oauth', [AppChannelYoutubeProfilesController::class, 'oauth'])->name('app.channelyoutubeprofiles.oauth');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('youtube', [AppChannelYoutubeProfilesController::class, 'settings'])->name('app.channelyoutubeprofiles.settings');
    });
});
