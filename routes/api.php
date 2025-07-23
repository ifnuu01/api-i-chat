<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-google-client', [GoogleController::class, 'testClientId']);
Route::post('/auth/google', [GoogleController::class, 'login']);

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'show']);
Route::delete('/user/{id}', [UserController::class, 'destroy']);
Route::post('/user/block/{id}', [UserController::class, 'block']);
Route::post('/user/unblock/{id}', [UserController::class, 'unblock']);
Route::get('/users/search', [UserController::class, 'search']);
