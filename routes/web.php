<?php

use App\Http\Controllers\AnniversaryController;
use App\Http\Controllers\AnniversaryWishLogController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BannerSliderController;
use App\Http\Controllers\BillTemplateController;
use App\Http\Controllers\BirthdayRecordController;
use App\Http\Controllers\BirthdayWishLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceReportController;
use App\Http\Controllers\InvoiceSendController;
use App\Http\Controllers\ItemAiController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MetalRateController;
use App\Http\Controllers\NoBusinessWhatsappController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RecycleController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserPlanController;
use App\Models\Plan;
use App\Models\UserPlan;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// frontend web routes:::::

Route::get('/', function () {
    $plans = Plan::where('status', 1)
        ->with(['planFeatures' => function ($query) {
            $query->where('is_active', 1)
                ->orderBy('sort_order', 'asc');
        }])
        ->orderByDesc('is_recommended')
        ->orderBy('sort_order', 'asc')
        ->orderBy('price', 'asc')
        ->get();

    return view('welcome', compact('plans'));
})->name('index');


Route::view('/privacy-policy', 'frontend.pages.privacy-policy')->name('privacy-policy');
Route::view('/terms-conditions', 'frontend.pages.terms-conditions')->name('terms-conditions');
Route::view('/refund-policy', 'frontend.pages.refund-policy')->name('refund-policy');
Route::view('/shipping-delivery-policy', 'frontend.pages.shipping-delivery-policy')->name('shipping-delivery-policy');
Route::view('/about-us', 'frontend.pages.about-us')->name('about-us');
Route::view('/contact-us', 'frontend.pages.contact-us')->name('contact-us');
Route::view('/pricing', 'frontend.pages.pricing')->name('pricing-page');













// Route::get('user-register', function (){
//     return view('user-register');
// })->name('index');

Route::get('user-register', function (\Illuminate\Http\Request $request) {
    $selectedPlan = null;

    if ($request->filled('plan_id')) {
        $selectedPlan = \App\Models\Plan::where('status', 1)
            ->where('id', $request->plan_id)
            ->first();
    }

    return view('user-register', compact('selectedPlan'));
})->name('user.register');
Route::post('/register', [HomeController::class, 'store'])->name('register.store');

Route::get('/welcome', function () {
    return view('welcome');
})->name('home');


// use App\Http\Controllers\RazorpayController;

// Route::middleware(['auth'])->group(function () {
//     Route::get('/razorpay/payment/{plan_id}', [RazorpayController::class, 'payment'])
//         ->name('razorpay.payment');

//     Route::post('/razorpay/payment/success', [RazorpayController::class, 'success'])
//         ->name('razorpay.success');
// });




Route::post('demo-requests/save', [DemoRequestController::class, 'store'])->name('demo-requests.save');

Volt::route('/super-admin/otp-verify', 'auth.super-admin-otp-verify')
    ->name('super-admin.otp.verify')
    ->middleware('guest');



Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register/send-otp', [RegisterController::class, 'sendEmailOtp'])->name('register.sendOtp');
Route::post('/register/verify-otp', [RegisterController::class, 'verifyEmailOtp'])->name('register.verifyOtp');
Route::post('/register/store', [RegisterController::class, 'store'])->name('register.store1');

//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::post('/metal-rates/today', [\App\Http\Controllers\MetalRateController::class, 'storeToday'])
    ->name('metal-rates.store-today');



Route::middleware(['auth'])->group(function () {



    Route::get('/choose-plan', [PlanController::class, 'choose'])->name('plan.choose');
    Route::post('/choose-plan', [PlanController::class, 'choosenSave'])->name('plan.choose.store');


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

        Route::post('/no-business/pdfs/delete/{invoice}', [InvoiceSendController::class, 'destroy'])
        ->name('no-business.pdfs.delete');



    Route::resource('birthday-wish-logs', BirthdayWishLogController::class)
        ->only(['index','show','store','update','destroy']);

    Route::post('birthday-wish-logs/{birthdayWishLog}/success', [BirthdayWishLogController::class, 'markSuccess'])
        ->name('birthday-wish-logs.success');

        Route::post('birthday-wish-logs/{birthdayWishLog}/resend', [BirthdayWishLogController::class, 'resend'])
        ->name('birthday-wish-logs.resend');

    Route::post('birthday-wish-logs/{birthdayWishLog}/failed', [BirthdayWishLogController::class, 'markFailed'])
        ->name('birthday-wish-logs.failed');

    Route::prefix('installment-reminders')->name('installment-reminders.')->controller(\App\Http\Controllers\InstallmentReminderController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('import', 'importForm')->name('import-form');
        Route::post('import', 'importStore')->name('import-store');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{installmentReminder}', 'edit')->name('edit');
        Route::get('show/{installmentReminder}', 'show')->name('show');
        Route::post('update/{installmentReminder}', 'update')->name('update');
        Route::delete('delete/{installmentReminder}', 'destroy')->name('destroy');
    });
});


        Route::get('invoices/{invoice}/view', [\App\Http\Controllers\InvoiceController::class, 'show'])
            ->name('invoices.show'); // View page



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

        // last bill of client
        Route::get('/invoices/client/{client}/last', [InvoiceController::class, 'getLastClientInvoice'])
            ->name('invoices.client.last');


        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/reports', [InvoiceReportController::class, 'reportsPage'])->name('reports.page');
            Route::get('/reports/download', [InvoiceReportController::class, 'export'])->name('reports.download');
        });


        Route::post('/clients/quick-store', [ClientController::class, 'quickStore'])->name('clients.quick-store');


        // photo upload and AI processing routes
        Route::get('/items/ai-photo-entry', [ItemAiController::class, 'create'])->name('items.ai.create');
        Route::post('/items/ai-photo-entry', [ItemAiController::class, 'photoEntry'])->name('items.ai-photo-entry');




        // Optional: item lookup by id (JSON). Not required if you preload items.
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])
            ->name('invoices.download');

        

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
        Route::post('restore/{user}', 'restore')->name('restore');
        Route::delete('force/{user}', 'forceDelete')->name('force');
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


    Route::resource('anniversaries', AnniversaryController::class);

    Route::get('anniversaries-import', [AnniversaryController::class, 'importForm'])
    ->name('anniversaries.importForm');

    Route::post('anniversaries-import', [AnniversaryController::class, 'import'])
        ->name('anniversaries.import');

    Route::resource('anniversaries', AnniversaryController::class);


    Route::resource('anniversary-wish-logs', AnniversaryWishLogController::class);

    Route::post('anniversary-wish-logs/{anniversaryWishLog}/resend', [AnniversaryWishLogController::class, 'resend'])
        ->name('anniversary-wish-logs.resend');

    Route::post('anniversary-wish-logs/{anniversaryWishLog}/success', [AnniversaryWishLogController::class, 'markSuccess'])
        ->name('anniversary-wish-logs.success');

    Route::post('anniversary-wish-logs/{anniversaryWishLog}/failed', [AnniversaryWishLogController::class, 'markFailed'])
        ->name('anniversary-wish-logs.failed');



    Route::prefix('bill-requests')->name('bill-requests.')->controller(\App\Http\Controllers\BillRequestController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('show/{billRequest}', 'show')->name('show');
        Route::delete('destroy/{billRequest}', 'destroy')->name('destroy');
        Route::post('create-invoice/{billRequest}', 'createInvoice')->name('create-invoice');
        Route::get('invoice/{billRequest}', 'showInvoice')->name('invoice');
    });


    Route::patch('demo-requests/{demoRequest}/status', [DemoRequestController::class, 'updateStatus'])
    ->name('demo-requests.update-status');

    Route::resource('demo-requests', DemoRequestController::class);


    Route::resource('plans', PlanController::class);
    Route::post('plans/{id}/toggle-status', [PlanController::class, 'toggleStatus'])->name('plans.toggleStatus');


    Route::get('bill-templates', [BillTemplateController::class, 'index'])->name('bill-templates.index');
    Route::get('bill-templates/create', [BillTemplateController::class, 'create'])->name('bill-templates.create');
    Route::post('bill-templates', [BillTemplateController::class, 'store'])->name('bill-templates.store');
    Route::get('bill-templates/choose', [BillTemplateController::class, 'choose'])->name('bill-templates.choose');
    Route::post('bill-templates/choose', [BillTemplateController::class, 'saveChosen'])->name('bill-templates.saveChosen');
    Route::get('bill-templates/{id}', [BillTemplateController::class, 'show'])->name('bill-templates.show');
    Route::get('bill-templates/{id}/edit', [BillTemplateController::class, 'edit'])->name('bill-templates.edit');
    Route::put('bill-templates/{id}', [BillTemplateController::class, 'update'])->name('bill-templates.update');
    Route::delete('bill-templates/{id}', [BillTemplateController::class, 'destroy'])->name('bill-templates.destroy');

    Route::resource('user-plans', UserPlanController::class);
    Route::get('user-plans.index1/{business}', [UserPlanController::class, 'index1'])->name('user-plans.index1');



    // RECYCLE BIN ROUTES
    Route::get('recycle', [RecycleController::class, 'index'])->name('recycle.index');

    Route::post('recycle/users/{id}/restore', [RecycleController::class, 'restoreUser'])->name('recycle.users.restore');
    Route::delete('recycle/users/{id}/force-delete', [RecycleController::class, 'forceDeleteUser'])->name('recycle.users.forceDelete');

    Route::post('recycle/businesses/{id}/restore', [RecycleController::class, 'restoreBusiness'])->name('recycle.businesses.restore');
    Route::delete('recycle/businesses/{id}/force-delete', [RecycleController::class, 'forceDeleteBusiness'])->name('recycle.businesses.forceDelete');

    Route::post('recycle/bulk-restore', [RecycleController::class, 'bulkRestore'])->name('recycle.bulkRestore');
    Route::delete('recycle/bulk-force-delete', [RecycleController::class, 'bulkForceDelete'])->name('recycle.bulkForceDelete');

    Route::delete('recycle/empty', [RecycleController::class, 'empty'])->name('recycle.empty');


    Route::get('/banner-sliders', [BannerSliderController::class, 'index'])
        ->name('banner-sliders.index');

    Route::get('/banner-sliders/create', [BannerSliderController::class, 'create'])
        ->name('banner-sliders.create');

    Route::post('/banner-sliders', [BannerSliderController::class, 'store'])
        ->name('banner-sliders.store');

    Route::get('/banner-sliders/{bannerSlider}', [BannerSliderController::class, 'show'])
        ->name('banner-sliders.show');

    Route::get('/banner-sliders/{bannerSlider}/edit', [BannerSliderController::class, 'edit'])
        ->name('banner-sliders.edit');

    Route::put('/banner-sliders/{bannerSlider}', [BannerSliderController::class, 'update'])
        ->name('banner-sliders.update');

    Route::delete('/banner-sliders/{bannerSlider}', [BannerSliderController::class, 'destroy'])
        ->name('banner-sliders.destroy');

    Route::patch('/banner-sliders/{bannerSlider}/toggle-status', [BannerSliderController::class, 'toggleStatus'])
        ->name('banner-sliders.toggle-status');
    

});



// routes/web.php
    Route::post('/switch-business', function(\Illuminate\Http\Request $r){
        $r->validate(['business_id'=>'required|exists:businesses,id']);
        abort_unless($r->user()->businesses()->where('business_id',$r->business_id)->exists(), 403);
        session(['active_business_id'=>$r->business_id]);
        return back()->with('success','Active business changed.');
    })->middleware('auth')->name('business.switch');


require __DIR__.'/auth.php';
