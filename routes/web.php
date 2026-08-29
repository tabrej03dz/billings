<?php

use App\Http\Controllers\AnniversaryController;
use App\Http\Controllers\AnniversaryWishLogController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BannerSliderController;
use App\Http\Controllers\BillTemplateController;
use App\Http\Controllers\BirthdayRecordController;
use App\Http\Controllers\BirthdayWishLogController;
use App\Http\Controllers\BusinessBillTemplateSettingController;
use App\Http\Controllers\BusinessTypeController;
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
use App\Http\Controllers\PlanPaymentController as ControllersPlanPaymentController;
use App\Http\Controllers\RecycleController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserPlanController;
use App\Models\BusinessType;
use App\Models\Plan;
use App\Models\PlanPaymentController;
use App\Models\UserPlan;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserActivityController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\AdRegistrationController;
use App\Http\Controllers\HospitalManagementController;
use App\Http\Controllers\OnboardingRegistrationController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BusinessCaController;
use App\Http\Controllers\CaReportController;

// frontend web routes:::::

Route::prefix('ad')
    ->name('ad.register.')
    ->group(function () {
        Route::get(
            '/',
            [AdRegistrationController::class, 'show']
        )->name('page');

        Route::post(
            '/save-step',
            [AdRegistrationController::class, 'saveStep']
        )->name('save-step');

        Route::post(
            '/complete',
            [AdRegistrationController::class, 'complete']
        )->name('complete');
    });

/*
|--------------------------------------------------------------------------
| OTP
|--------------------------------------------------------------------------
*/

Route::post(
    '/register/send-otp',
    [RegisterController::class, 'sendOtp']
)->name('register.sendOtp');

Route::post(
    '/register/verify-otp',
    [RegisterController::class, 'verifyOtp']
)->name('register.verifyOtp');

/*
|--------------------------------------------------------------------------
| Choose plan
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {





    // ==========================================
    // CA Management - Business Side
    // ==========================================

    // CA Manage Page
        Route::get('/ca/manage', [BusinessCaController::class, 'index'])
            ->name('ca.manage');

        // Assign CA
        Route::post('/ca/assign', [BusinessCaController::class, 'store'])
            ->name('business.ca.store');

        // Revoke CA
        Route::delete('/ca/{assignment}/destroy', [BusinessCaController::class, 'destroy'])
            ->name('business.ca.destroy');

        // Reactivate CA
        Route::patch('/ca/{assignment}/reactivate', [BusinessCaController::class, 'reactivate'])
        ->name('business.ca.reactivate');


        // CA Reports
        Route::get('/ca/reports', [CaReportController::class, 'index'])
            ->name('ca.reports');

        Route::get('/ca/reports/download', [CaReportController::class, 'download'])
            ->name('ca.reports.download');



    Route::get(
        '/choose-plan',
        [PlanController::class, 'choose']
    )->name('plan.choose');

    Route::post(
        '/choose-plan/save',
        [PlanController::class, 'choosenSave']
    )->name('plan.choosen-save');


     Route::get('/units', [UnitController::class, 'index'])
        ->name('units.index');

    Route::post('/units', [UnitController::class, 'store'])
        ->name('units.store');

    Route::get('/units/{unit}', [UnitController::class, 'show'])
        ->name('units.show');

    Route::put('/units/{unit}', [UnitController::class, 'update'])
        ->name('units.update');

    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])
        ->name('units.destroy');

    Route::post('/units-quick-store', [UnitController::class, 'quickStore'])
        ->name('units.quick-store');
});


Route::prefix('onboarding-registrations')
    ->name('onboarding-registrations.')
    ->controller(OnboardingRegistrationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{onboardingRegistration}', 'show')->name('show');
        Route::get('/{onboardingRegistration}/edit', 'edit')->name('edit');
        Route::put('/{onboardingRegistration}', 'update')->name('update');
        Route::delete('/{onboardingRegistration}', 'destroy')->name('destroy');

        Route::patch(
            '/{onboardingRegistration}/verify-phone',
            'markPhoneVerified'
        )->name('verify-phone');

        Route::patch(
            '/{onboardingRegistration}/unverify-phone',
            'markPhoneUnverified'
        )->name('unverify-phone');

        Route::patch(
            '/{onboardingRegistration}/complete',
            'markCompleted'
        )->name('complete');

        Route::patch(
            '/{onboardingRegistration}/status',
            'changeStatus'
        )->name('change-status');
    });

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

Route::get('/welcome', function () {
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
})->name('home');


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
    $businessTypes = BusinessType::query()
        ->orderBy('name')
        ->get();

    return view('user-register', compact('selectedPlan', 'businessTypes'));
})->name('user.register');
Route::post('/register', [HomeController::class, 'store'])->name('register.store');

// Route::get('/welcome', function () {
//     return view('welcome');
// })->name('home');


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
// Route::post('/register/send-otp', [RegisterController::class, 'sendEmailOtp'])->name('register.sendOtp');
// Route::post('/register/verify-otp', [RegisterController::class, 'verifyEmailOtp'])->name('register.verifyOtp');

Route::post('/register/send-otp', [RegisterController::class, 'sendPhoneOtp'])
    ->name('register.sendOtp');

Route::post('/register/verify-otp', [RegisterController::class, 'verifyPhoneOtp'])
    ->name('register.verifyOtp');
Route::post('/register/store', [RegisterController::class, 'store'])->name('register.store1');

    Route::get('/plan/payment/{plan}', [ControllersPlanPaymentController::class, 'show'])
        ->name('plan.payment');

        Route::get('/plans/{plan}/payment', [ControllersPlanPaymentController::class, 'show'])->name('plans.payment');
        Route::post('/plans/{plan}/payment/order', [ControllersPlanPaymentController::class, 'createOrder'])->name('plans.payment.order');
        Route::post('/plans/{plan}/payment/success', [ControllersPlanPaymentController::class, 'success'])->name('plans.payment.success');



//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::post('/metal-rates/today', [\App\Http\Controllers\MetalRateController::class, 'storeToday'])
    ->name('metal-rates.store-today');



Route::middleware(['auth'])->group(function () {








    // ACTIVITY LOG ROUTES

 Route::post(
        '/activity/heartbeat',
        [UserActivityController::class, 'heartbeat']
    )->name('activity.heartbeat');

    Route::post(
        '/activity/end',
        [UserActivityController::class, 'end']
    )->name('activity.end');

    Route::post(
    '/activity/error',
    [UserActivityController::class, 'storeError']
)->name('activity.error');

    /*
    |--------------------------------------------------------------------------
    | Super Admin User Activity Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('super-admin/user-activity')
        ->name('super-admin.user-activity.')
        ->group(function () {

            Route::get(
                '/',
                [UserActivityController::class, 'index']
            )->name('index');

            Route::get(
                '/users/{user}',
                [UserActivityController::class, 'show']
            )->name('show');

            Route::delete(
                '/users/{user}/clear',
                [UserActivityController::class, 'clearUserActivity']
            )->name('clear-user');
        });




        Route::prefix('business-profile')
    ->name('business-profile.')
    ->controller(BusinessProfileController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');

        Route::put('/', 'update')->name('update');

        Route::post(
            '/template/{billTemplate}',
            'selectTemplate'
        )->name('template.select');

        Route::post(
            '/dismiss-suggestion',
            'dismissSuggestion'
        )->name('suggestion.dismiss');
    });

Route::prefix('hospital')
    ->name('hospital.')
    ->controller(HospitalManagementController::class)
    ->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');

        Route::get('/doctors', 'doctors')->name('doctors.index');
        Route::post('/doctors', 'storeDoctor')->name('doctors.store');
        Route::put('/doctors/{doctor}', 'updateDoctor')->name('doctors.update');
        Route::delete('/doctors/{doctor}', 'deleteDoctor')->name('doctors.destroy');

        Route::get('/patients', 'patients')->name('patients.index');
        Route::post('/patients', 'storePatient')->name('patients.store');
        Route::put('/patients/{patient}', 'updatePatient')->name('patients.update');

        Route::get('/departments', 'departments')->name('departments.index');
        Route::post('/departments', 'storeDepartment')->name('departments.store');
        Route::put('/departments/{department}', 'updateDepartment')->name('departments.update');
        Route::delete('/departments/{department}', 'deleteDepartment')->name('departments.destroy');

        Route::get('/wards', 'wards')->name('wards.index');
        Route::post('/wards', 'storeWard')->name('wards.store');
        Route::put('/wards/{ward}', 'updateWard')->name('wards.update');
        Route::delete('/wards/{ward}', 'deleteWard')->name('wards.destroy');

        Route::get('/rooms', 'rooms')->name('rooms.index');
        Route::post('/rooms', 'storeRoom')->name('rooms.store');
        Route::put('/rooms/{room}', 'updateRoom')->name('rooms.update');
        Route::delete('/rooms/{room}', 'deleteRoom')->name('rooms.destroy');

        Route::get('/beds', 'beds')->name('beds.index');
        Route::post('/beds', 'storeBed')->name('beds.store');
        Route::put('/beds/{bed}', 'updateBed')->name('beds.update');
        Route::delete('/beds/{bed}', 'deleteBed')->name('beds.destroy');

        Route::get('/visits', 'visits')->name('visits.index');
    });



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



        Route::get('/clients/{client}/report/pdf', [ClientController::class, 'exportPdf'])
        ->name('clients.report.pdf');

    Route::get('/clients/{client}/report/excel', [ClientController::class, 'exportExcel'])
        ->name('clients.report.excel');

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
        // Route::get('create', 'create')->name('create');
        Route::get('edit/{item}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::put('update/{item}', 'update')->name('update');
        Route::delete('destroy/{item}', 'destroy')->name('destroy');
        Route::post('store/ajax', 'storeAjax')->name('store.ajax');


        /*
        |--------------------------------------------------------------------------
        | Barcode routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            'barcode/lookup',
            [\App\Http\Controllers\ItemBarcodeController::class, 'lookup']
        )->name('barcode.lookup');

        Route::post(
            'barcodes/generate-missing',
            [\App\Http\Controllers\ItemBarcodeController::class, 'generateMissing']
        )->name('barcodes.generate-missing');

        Route::post(
            'barcodes/print',
            [\App\Http\Controllers\ItemBarcodeController::class, 'printBulk']
        )->name('barcodes.print');

        Route::post(
            '{item}/barcode/generate',
            [\App\Http\Controllers\ItemBarcodeController::class, 'generate']
        )->name('barcode.generate');

        Route::get(
            '{item}/barcode/print',
            [\App\Http\Controllers\ItemBarcodeController::class, 'printOne']
        )->name('barcode.print');
    });

    Route::get('item/create', [\App\Http\Controllers\ItemController::class, 'create'])->name('items.create');

    Route::post('/purchases/suppliers',[PurchaseController::class, 'storeSupplier'])->name('purchases.suppliers.store');


    Route::resource('purchases', PurchaseController::class);

    // routes/web.php
    Route::get('/inventory/summary', [\App\Http\Controllers\InventoryController::class, 'summary'])
        ->name('inventory.summary');



    Route::get(
        '/businesses/users/search',
        [App\Http\Controllers\BusinessController::class, 'searchUsers']
    )->name('businesses.users.search');


    Route::prefix('businesses')->name('businesses.')->controller(\App\Http\Controllers\BusinessController::class)->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{business}', 'edit')->name('edit');
        Route::put('update/{business}', 'update')->name('update');
        Route::delete('delete/{business}', 'destroy')->name('delete');
    });

    Route::post(
        '/categories/quick-store',
        [CategoryController::class, 'quickStore']
    )->name('categories.quick-store');

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
    Route::get('bill-templates/customize/{template}', [BillTemplateController::class, 'customize'])->name('bill-templates.customize');


    Route::get('/bill-template/customize/{template}', [BusinessBillTemplateSettingController::class, 'edit'])
        ->name('bill-template.customize');

    Route::post('/bill-template/customize/{template}', [BusinessBillTemplateSettingController::class, 'save'])
        ->name('bill-template.customize.save');

    Route::post('bill-templates/customize/reset/{template}', [BusinessBillTemplateSettingController::class, 'resetCustomize'])
        ->name('bill-template.customize.reset');

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

        // business types

    Route::resource('business-types', BusinessTypeController::class);



    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])
        ->name('users.impersonate');

    Route::post('/impersonate/exit', [UserController::class, 'exitImpersonate'])
        ->name('impersonate.exit');
    

});



// routes/web.php
    Route::post('/switch-business', function(\Illuminate\Http\Request $r){
        $r->validate(['business_id'=>'required|exists:businesses,id']);
        abort_unless($r->user()->businesses()->where('business_id',$r->business_id)->exists(), 403);
        session(['active_business_id'=>$r->business_id]);
        return back()->with('success','Active business changed.');
    })->middleware('auth')->name('business.switch');




//     Route::get('/font-check', function () {
//     $regular = storage_path('fonts/NotoSansDevanagari-Regular.ttf');
//     $bold    = storage_path('fonts/NotoSansDevanagari-Bold.ttf');

//     return response()->json([
//         'storage_path' => storage_path(),
//         'regular_path' => $regular,
//         'regular_exists' => file_exists($regular),
//         'regular_readable' => is_readable($regular),
//         'bold_path' => $bold,
//         'bold_exists' => file_exists($bold),
//         'bold_readable' => is_readable($bold),
//     ]);
// });
require __DIR__.'/auth.php';
