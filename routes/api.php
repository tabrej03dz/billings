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
        Route::get('/businesses/index', [BusinessController::class, 'index']);          // list
        Route::post('/businesses/store', [BusinessController::class, 'store']);         // create
        Route::post('/businesses/update/{business}', [BusinessController::class, 'update']); // update (supports file)
        Route::delete('/businesses/delete/{business}', [BusinessController::class, 'destroy']); // delete
    });

});

Route::get('/user', [HomeController::class, 'user'])->middleware('auth:sanctum');


