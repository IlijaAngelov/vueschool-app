<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Testing
Route::get('/test', function () {
    return response()->json([
        'version' => '1.0',
        'latest_update' => '1 min ago',
        'data' => 'This is the data received'
    ]);
})->middleware('throttle:test');

Route::get('/users', [UserController::class, 'users'])->middleware('throttle:batch');

Route::get('/individuals', [UserController::class, 'individuals'])->middleware('throttle:individuals');

Route::get('/user/{id}', [UserController::class, 'user'])->middleware('throttle:user');
