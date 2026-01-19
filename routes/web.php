<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BirthdayRecordController;
use App\Http\Controllers\BirthdayWishLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MetalRateController;
use App\Http\Controllers\NoBusinessWhatsappController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


// frontend web routes:::::
Route::get('/', function (){
    return view('welcome');
})->name('index');

Route::get('/welcome', function () {
    return view('welcome');
})->name('home');

//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::post('/metal-rates/today', [\App\Http\Controllers\MetalRateController::class, 'storeToday'])
    ->name('metal-rates.store-today');



Route::middleware(['auth'])->group(function () {

    Route::prefix('invoice-sends')->name('invoice-sends.')->controller(\App\Http\Controllers\InvoiceSendController::class)->group(function(){
        Route::get('/', 'index')->name('index');
    });

    Route::get('/no-business/whatsapp', [NoBusinessWhatsappController::class, 'index'])
        ->name('no-business.whatsapp');

    Route::get('/no-business/whatsapp/drop', [NoBusinessWhatsappController::class, 'drop'])
        ->name('no-business.whatsapp.drop');

    Route::post('/no-business/whatsapp/send-pdf', [NoBusinessWhatsappController::class, 'sendInvoiceWhatsapp'])
        ->name('no-business.send-pdf');

    Route::get('/no-business/api-settings', [NoBusinessWhatsappController::class, 'apiSettings'])
        ->name('no-business.api-settings');

    Route::post('/no-business/whatsapp/save-api', [NoBusinessWhatsappController::class, 'saveApi'])
        ->name('no-business.save-api');

    Route::get('send-invoice-whatsapp', [\App\Http\Controllers\InvoiceSendController::class, 'index'])->name('send-invoice-whatsapp.index');



    Route::post('/no-business/send-pdf-dropzone', [NoBusinessWhatsappController::class, 'sendInvoiceWhatsappDropzone'])
        ->name('no-business.send-pdf-dropzone');

    // web.php
    Route::post('/no-business/pdfs/upload', [NoBusinessWhatsappController::class, 'uploadPdfQueue'])
        ->name('no-business.pdfs.upload');

    Route::post('/no-business/pdfs/send-queued', [NoBusinessWhatsappController::class, 'sendQueuedPdfs'])
        ->name('no-business.pdfs.sendQueued');

    Route::get('/no-business/pdfs/retry/{invoice}', [NoBusinessWhatsappController::class, 'sendPdfRetry'])
        ->name('no-business.pdfs.retry');



    Route::resource('birthday-wish-logs', BirthdayWishLogController::class)
        ->only(['index','show','store','update','destroy']);

    Route::post('birthday-wish-logs/{birthdayWishLog}/success', [BirthdayWishLogController::class, 'markSuccess'])
        ->name('birthday-wish-logs.success');

    Route::post('birthday-wish-logs/{birthdayWishLog}/failed', [BirthdayWishLogController::class, 'markFailed'])
        ->name('birthday-wish-logs.failed');

    Route::prefix('installment-reminders')->name('installment-reminders.')->controller(\App\Http\Controllers\InstallmentReminderController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('import', 'importForm')->name('import-form');
        Route::post('import', 'importStore')->name('import-store');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{installment}', 'edit')->name('edit');
        Route::post('update/{installment}', 'update')->name('update');
        Route::delete('delete/{installment}', 'delete')->name('destroy');
    });
});










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


    Route::resource('api-keys', \App\Http\Controllers\ApiKeyController::class);

        Route::get('/invoices',              [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create/{type?}',       [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices/store/{type}',             [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::get('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::put('/invoices/{invoice}',    [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        Route::post('/invoices/{invoice}/convert-to-tax', [InvoiceController::class, 'convertToTax'])
            ->name('invoices.convertToTax');
        Route::post('/invoices/{invoice}/payment-in', [InvoiceController::class, 'paymentIn'])
            ->name('invoices.paymentIn');

        Route::get('/invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');





    Route::post('/clients/quick-store', [ClientController::class, 'quickStore'])->name('clients.quick-store');

        // Optional: item lookup by id (JSON). Not required if you preload items.
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])
            ->name('invoices.download');
        Route::get('invoices/{invoice}/view', [\App\Http\Controllers\InvoiceController::class, 'show'])
            ->name('invoices.show'); // View page

    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'preview'])
        ->name('invoices.preview');

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
        Route::get('show/{client}', 'show')->name('show');

    });

    Route::prefix('items')->name('items.')->controller(\App\Http\Controllers\ItemController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('edit/{item}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::put('update/{item}', 'update')->name('update');
        Route::delete('destroy/{item}', 'destroy')->name('destroy');
        Route::post('store/ajax', 'storeAjax')->name('store.ajax');
    });

    Route::get('item/create', [\App\Http\Controllers\ItemController::class, 'create'])->name('item.create');

    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);

    // routes/web.php
    Route::get('/inventory/summary', [\App\Http\Controllers\InventoryController::class, 'summary'])
        ->name('inventory.summary');




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


    Route::resource('metal-rates', MetalRateController::class);

    Route::post('metal-rates/toggle/{metalRate}', [MetalRateController::class, 'toggle'])->name('metal-rates.toggle');

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
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{user}', 'edit')->name('edit');
        Route::post('update/{user}', 'update')->name('update');

        // ✅ Permission destroy (POST)
        Route::post('destroy/{permission}', 'destroy')->name('destroy');
    });

    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('assign', 'assign')->name('assign');   // user ko roles assign
        Route::post('store', 'store')->name('store');      // role create
        Route::post('destroy/{role}', 'destroy')->name('destroy'); // role delete
    });

    Route::post('/roles/{role}/permissions/sync', [RoleController::class, 'syncPermissions'])
        ->name('roles.permissions.sync');

    Route::prefix('bank-accounts')->name('bank-accounts.')->middleware(['auth'])->group(function () {
        Route::get('/index/{business?}', [BankAccountController::class, 'index'])->name('index');

        Route::get('/create/{business?}', [BankAccountController::class, 'create'])->name('create');
        Route::post('/store/{business?}', [BankAccountController::class, 'store'])->name('store');

        Route::get('/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('edit');
        Route::put('/{bankAccount}', [BankAccountController::class, 'update'])->name('update');

        Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');

        // Make default
        Route::post('/{bankAccount}/default', [BankAccountController::class, 'makeDefault'])->name('default');
    });




    Route::get('/birthday-records/import', [BirthdayRecordController::class, 'importForm'])
        ->name('birthday-records.importForm');
    Route::post('/birthday-records/import', [BirthdayRecordController::class, 'import'])
        ->name('birthday-records.import');
    Route::resource('/birthday-records', BirthdayRecordController::class);
    Route::get('birthday-records/send/{birthdayRecord}', [BirthdayRecordController::class, 'send'])->name('birthday-records.send');


});



// routes/web.php
    Route::post('/switch-business', function(\Illuminate\Http\Request $r){
        $r->validate(['business_id'=>'required|exists:businesses,id']);
        abort_unless($r->user()->businesses()->where('business_id',$r->business_id)->exists(), 403);
        session(['active_business_id'=>$r->business_id]);
        return back()->with('success','Active business changed.');
    })->middleware('auth')->name('business.switch');


require __DIR__.'/auth.php';
