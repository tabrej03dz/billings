<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

//    Route::prefix('invoices')->name('invoices.')->controller(\App\Http\Controllers\InvoiceController::class)->group(function(){
//        Route::get('/', 'index')->name('index');
//        Route::get('create', 'create')->name('create');
//        Route::post('store', 'store')->name('store');
//        Route::get('show/{invoice}', 'show')->name('show');
//        Route::get('download/{invoice}', 'download')->name('download');
//    });



        Route::get('/invoices',              [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create',       [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices',             [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}',    [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');



        Route::post('/clients/quick-store', [ClientController::class, 'quickStore'])->name('clients.quick-store');

        // Optional: item lookup by id (JSON). Not required if you preload items.
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])
            ->name('invoices.download');
        Route::get('invoices/{invoice}/view', [\App\Http\Controllers\InvoiceController::class, 'show'])
            ->name('invoices.show'); // View page

        Route::post('/invoices/preview-number', [InvoiceController::class, 'previewNumber'])
            ->name('invoices.preview-number');


        Route::resource('additional-charges', \App\Http\Controllers\AdditionalChargeController::class)
            ->only(['index','create','store','edit','update','destroy']);


    Route::prefix('clients')->name('clients.')->controller(\App\Http\Controllers\ClientController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('edit/{client}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::put('update/{client}', 'update')->name('update');
        Route::delete('destroy/{client}', 'destroy')->name('destroy');
    });

    Route::prefix('items')->name('items.')->controller(\App\Http\Controllers\ItemController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('edit/{item}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::put('update/{item}', 'update')->name('update');
        Route::delete('destroy/{item}', 'destroy')->name('destroy');
    });





    Route::prefix('businesses')->name('businesses.')->controller(\App\Http\Controllers\BusinessController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{business}', 'edit')->name('edit');
        Route::put('update/{business}', 'update')->name('update');
        Route::delete('delete/{business}', 'destroy')->name('delete');
    });

//    Route::prefix('categories')->name('categories.')->controller(\App\Http\Controllers\CategoryController::class)->group(function(){
//        Route::get('/', 'index')->name('index');
//        Route::get('create', 'create')->name('create');
//        Route::post('store', 'store')->name('store');
//        Route::get('edit/{category}', 'edit')->name('edit');
//        Route::post('update/{category}', 'update')->name('update');
//        Route::post('delete/{category}', 'delete')->name('delete');
//    });

    Route::resource('categories', CategoryController::class);

    Route::prefix('users')->name('users.')->controller(\App\Http\Controllers\UserController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{user}', 'edit')->name('edit');
        Route::put('update/{user}', 'update')->name('update');
        Route::delete('destroy/{user}', 'destroy')->name('destroy');
        Route::get('permissions/{user}', 'permissions')->name('permissions');
        Route::get('permission/remove/{user}/{permission}', 'permissionRemove')->name('permission.remove');
    });

    Route::prefix('permissions')->name('permissions.')->controller(\App\Http\Controllers\PermissionController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::post('assign', 'assign')->name('assign');
//        Route::get('create', 'create')->name('create');
//        Route::post('store', 'store')->name('store');
//        Route::get('edit/{user}', 'edit')->name('edit');
//        Route::post('update/{user}', 'update')->name('update');
//        Route::post('delete/{user}', 'delete')->name('delete');
    });
});



// routes/web.php
Route::post('/switch-business', function(\Illuminate\Http\Request $r){
    $r->validate(['business_id'=>'required|exists:businesses,id']);
    abort_unless($r->user()->businesses()->where('business_id',$r->business_id)->exists(), 403);
    session(['active_business_id'=>$r->business_id]);
    return back()->with('success','Active business changed.');
})->middleware('auth')->name('business.switch');


require __DIR__.'/auth.php';
