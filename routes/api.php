<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfflinePosSyncController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/pos/offline-sales', [OfflinePosSyncController::class, 'store'])
    ->name('api.pos.offline-sales.store');
