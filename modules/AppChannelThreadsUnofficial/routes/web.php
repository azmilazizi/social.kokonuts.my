<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelThreadsUnofficial\Http\Controllers\AppChannelThreadsUnofficialController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(['prefix' => 'app'], function () {
        Route::group(['prefix' => 'threads'], function () {
            Route::group(['prefix' => 'profile'], function () {
                Route::resource('/', AppChannelThreadsUnofficialController::class)->names('app.channelthreadsunoofficial');
                Route::get('oauth', [AppChannelThreadsUnofficialController::class, 'oauth'])->name('app.channelthreadsunoofficial.oauth');
                Route::post('proccess', [AppChannelThreadsUnofficialController::class, 'proccess'])->name('app.channelthreadsunoofficial.proccess');
            });
        });
    });

    Route::group(['prefix' => 'admin/api-integration'], function () {
        Route::get('threads', [AppChannelThreadsUnofficialController::class, 'settings'])->name('app.channelthreadsunoofficial.settings');
    });
});
