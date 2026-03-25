<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GhlOauthController;
use App\Http\Controllers\ProviderConfigController;

Route::get('/', function () {
    return view('welcome');
});

// GoHighLevel OAuth Routes
Route::get('/oauth/install', [GhlOauthController::class, 'install'])->name('oauth.install');
Route::get('/oauth/callback', [GhlOauthController::class, 'callback'])->name('oauth.callback');

// Configuration Routes
Route::middleware(['ghl.auth'])->group(function () {
    Route::get('/config', [ProviderConfigController::class, 'show'])->name('config.show');
    Route::post('/config', [ProviderConfigController::class, 'save'])->name('config.save');
});
