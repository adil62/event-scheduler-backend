<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
 
Route::group([ 
    'prefix' => 'auth' 
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::group([ 
    'prefix' => 'event' ,
    'middleware' => 'auth:api'
], function () {
    Route::post('/', [EventController::class, 'create']);
    Route::get('/', [EventController::class, 'index']);
});