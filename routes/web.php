<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Testing
Route::get('/test', function () {
    return response()->json([
        'version' => '1.0',
        'latest_update' => '1 min ago',
        'data' => 'This is the data received'
    ]);
})->middleware('throttle:test');

Route::get('/users', [UserController::class, 'users'])->middleware('throttle:batch');

Route::get('/user/{id}', [UserController::class, 'getApiUser'])->middleware('throttle:updateUser');
Route::get('user/{id}/update', [UserController::class, 'update'])->middleware('throttle:updateUser');

Route::get('/user/update/{id}', [UserController::class, 'checkForUserUpdate'])->middleware('throttle:updateUser');
