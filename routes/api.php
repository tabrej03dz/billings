<?php

use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceSendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BirthdayWishController;
use App\Http\Controllers\Api\HomeController;

//Route::post('items/store', [\App\Http\Controllers\Api\ItemController::class, 'store']);

Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);

Route::post('/login',  [HomeController::class, 'login']);


Route::middleware('auth:sanctum', 'active.business')->group(function () {

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






    // INVOICE APIS

    Route::prefix('invoices')->name('invoices.')->controller(InvoiceController::class)->group(function(){
        Route::get('index/{type}', [InvoiceController::class, 'index']);

        // create (docType: tax | proforma | quotation)
        Route::post('store/{docType}', [InvoiceController::class, 'store']);

        // show invoice json
        Route::get('/{invoice}', [InvoiceController::class, 'show']);

        // update
        Route::post('update/{invoice}', [InvoiceController::class, 'update']);

        // delete
        Route::delete('delete/{invoice}', [InvoiceController::class, 'destroy']);

        // pdf view/download links
        Route::get('pdf/show/{invoice}', [InvoiceController::class, 'show']);         // stream
        Route::get('pdf-url/{invoice}/pdf-url', [InvoiceController::class, 'pdfUrl']); // public url

        // preview invoice number (prefix + date)
        Route::post('/invoice-number/{type?}', [InvoiceController::class, 'preview']);

        // convert quotation/proforma -> tax
        Route::post('/{invoice}/convert-to-tax', [InvoiceController::class, 'convertToTax']);
    });




    // api for set external api keys

    Route::prefix('api-keys')->controller(App\Http\Controllers\Api\ApiKeyController::class)->group(function (){
       Route::get('index', 'index');
       Route::post('store', 'store');
       Route::delete('delete/{api}', 'destroy');
    });

    Route::post('/whatsapp/upload-send-pdf', [InvoiceSendController::class, 'uploadAndSendPdf']);








});

Route::get('/user', [HomeController::class, 'user'])->middleware('auth:sanctum');
Route::post('/user/profile-update', [HomeController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::post('/user/change-password', [HomeController::class, 'changePassword'])->middleware('auth:sanctum');
Route::get('/dashboard', [HomeController::class, 'index'])->middleware('auth:sanctum');
Route::get('/user/permissions', [HomeController::class, 'myPermissions'])->middleware('auth:sanctum');


