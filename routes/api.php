<?php

use App\Http\Controllers\Api\SccController;
use Illuminate\Support\Facades\Route;

Route::prefix('scc')->group(function () {
    Route::post('/data', [SccController::class, 'store']);
    Route::get('/latest', [SccController::class, 'latest']);
    Route::get('/history', [SccController::class, 'history']);
});
