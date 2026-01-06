<?php

use App\Http\Controllers\Api\BusinessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BirthdayWishController;
use App\Http\Controllers\Api\HomeController;


Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);

Route::post('/login',  [HomeController::class, 'login']);

//Route::post('clients/store', [\App\Http\Controllers\Api\ClientController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [HomeController::class, 'logout']);


    Route::prefix('businesses')->group(function(){
        Route::get('index', [BusinessController::class, 'index']);          // list
        Route::post('store', [BusinessController::class, 'store']);         // create
        Route::post('update/{business}', [BusinessController::class, 'update']); // update (supports file)
        Route::delete('delete/{business}', [BusinessController::class, 'destroy']); // delete
    });

    Route::prefix('users')->controller(\App\Http\Controllers\Api\UserController::class)->group(function(){
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::put('/update/{user}', 'update');   // or PATCH
        Route::delete('/delete/{user}', 'destroy');
    });


    Route::prefix('clients')->controller(\App\Http\Controllers\Api\ClientController::class)->group(function(){
        Route::get('/index', 'index');
        Route::post('store', 'store'); // ✅ merged store

        Route::post('update/{client}', 'update');
//        Route::patch('/{client}', 'update');
        Route::delete('delete/{client}', 'destroy');
    });


});

Route::get('/user', [HomeController::class, 'user'])->middleware('auth:sanctum');


