<?php

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

    Route::prefix('invoices')->name('invoices.')->controller(\App\Http\Controllers\InvoiceController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('show/{invoice}', 'show')->name('show');
        Route::get('download/{invoice}', 'download')->name('download');
    });

    Route::prefix('clients')->name('clients.')->controller(\App\Http\Controllers\ClientController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
    });

    Route::prefix('businesses')->name('businesses.')->controller(\App\Http\Controllers\BusinessController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{business}', 'edit')->name('edit');
        Route::post('update/{business}', 'update')->name('update');
        Route::post('delete/{business}', 'delete')->name('delete');
    });

    Route::prefix('users')->name('users.')->controller(\App\Http\Controllers\UserController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{user}', 'edit')->name('edit');
        Route::post('update/{user}', 'update')->name('update');
        Route::post('delete/{user}', 'delete')->name('delete');
    });
});

require __DIR__.'/auth.php';
