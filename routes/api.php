<?php

use App\Http\Controllers\Api\BusinessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BirthdayWishController;
use App\Http\Controllers\Api\HomeController;


Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);

Route::post('/login',  [HomeController::class, 'login']);
Route::post('/logout', [HomeController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('businesses')->group(function(){
        Route::get('index', [BusinessController::class, 'index']);          // list
        Route::post('store', [BusinessController::class, 'store']);         // create
        Route::post('update/{business}', [BusinessController::class, 'update']); // update (supports file)
        Route::delete('delete/{business}', [BusinessController::class, 'destroy']); // delete
    });

});

Route::get('/user', [HomeController::class, 'user'])->middleware('auth:sanctum');


