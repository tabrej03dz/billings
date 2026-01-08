<?php

use App\Http\Controllers\Api\BusinessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BirthdayWishController;
use App\Http\Controllers\Api\HomeController;

//Route::post('items/store', [\App\Http\Controllers\Api\ItemController::class, 'store']);

Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);

Route::post('/login',  [HomeController::class, 'login']);


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

    Route::prefix('categories')->controller(\App\Http\Controllers\Api\CategoryController::class)->group(function(){
       Route::get('/', 'index');
       Route::post('store', 'store');
       Route::post('update/{category}', 'update');
       Route::delete('delete/{category}', 'destroy');
    });

    Route::prefix('items')->controller(\App\Http\Controllers\Api\ItemController::class)->group(function(){
        Route::get('/', 'index');
        Route::post('store', 'store');
        Route::post('update/{item}', 'update');
        Route::delete('delete/{item}', 'destroy');
    });




});

Route::get('/user', [HomeController::class, 'user'])->middleware('auth:sanctum');


