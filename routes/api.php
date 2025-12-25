<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BirthdayWishController;


Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


