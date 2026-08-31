<?php



// use App\Http\Controllers\Api\BannerSliderController;
// use App\Http\Controllers\Api\BillRequestController as ApiBillRequestController;
// use App\Http\Controllers\Api\BillTemplateController;
// use App\Http\Controllers\Api\BirthdayWishController;
// use App\Http\Controllers\Api\BusinessController;
// use App\Http\Controllers\Api\HomeController;
// use App\Http\Controllers\Api\InstallmentReminderController;
// use App\Http\Controllers\Api\InvoiceController;
// use App\Http\Controllers\Api\InvoiceSendController;
// use App\Http\Controllers\Api\ItemBarcodeController;
// use App\Http\Controllers\Api\ItemController;
// use App\Http\Controllers\Api\HospitalManagementController;
// use App\Http\Controllers\Api\PlanController;
// use App\Http\Controllers\Api\PlanPaymentController;
// use App\Http\Controllers\Api\PurchaseController;
// use App\Http\Controllers\Api\UnitController;
// use Illuminate\Support\Facades\Route;

// /*
// |--------------------------------------------------------------------------
// | Public API Routes
// |--------------------------------------------------------------------------
// */

// Route::post('/login', [HomeController::class, 'login']);
// Route::post('/login/verify-otp', [HomeController::class, 'verifyLoginOtp']);
// Route::post('/register', [HomeController::class, 'register']);
// Route::post('/register/verify-otp', [HomeController::class, 'verifyRegisterOtp']);

// Route::post('/plans/{plan}/create-order', [PlanPaymentController::class, 'createOrder']);
// Route::post('/plans/payment/verify', [PlanPaymentController::class, 'verifyPayment']);

// Route::post('/request-for-bill', [ApiBillRequestController::class, 'store']);
// Route::get('/bill-requests/index/api', [ApiBillRequestController::class, 'index1']);
// Route::get('/items1', [ItemController::class, 'index1']);

// /*
// | These scheduler routes should ideally be protected by a secret-token
// | middleware or moved to console scheduler commands.
// */
// Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);
// Route::get('/installment-reminders/run', [\App\Http\Controllers\InstallmentReminderController::class, 'run']);
// Route::get('/drive-scan-pdf/run', [\App\Http\Controllers\InstallmentReminderController::class, 'driveScanPdf']);
// Route::get('/send-uploaded-invoice/run', [\App\Http\Controllers\InstallmentReminderController::class, 'sendUploadedInvoice']);

// /*
// |--------------------------------------------------------------------------
// | Authenticated API Routes
// |--------------------------------------------------------------------------
// |
// | Spatie middleware syntax:
// |   permission:permission name
// |   permission:first permission|second permission  => OR condition
// |
// */
// Route::middleware(['auth:sanctum', 'active.business'])->group(function () {

//     Route::post('/logout', [HomeController::class, 'logout']);

//     Route::post('/delete-account/send-otp', [HomeController::class, 'sendDeleteAccountOtp'])
//         ->middleware('throttle:3,10');

//     Route::post('/delete-account', [HomeController::class, 'deleteAccount'])
//         ->middleware('throttle:5,10');

//     /* Business */
//     Route::prefix('businesses')->group(function () {
//         Route::get('/index', [BusinessController::class, 'index']);

//         Route::post('/store', [BusinessController::class, 'store']);

//         Route::post('/update/{business}', [BusinessController::class, 'update']);

//         Route::delete('/delete/{business}', [BusinessController::class, 'destroy'])
//             ->middleware('permission:delete business');
//     });

//     /* Users */
//     Route::prefix('users')
//         ->controller(\App\Http\Controllers\Api\UserController::class)
//         ->group(function () {
//             Route::get('/index', 'index')
//                 ->middleware('permission:show users|view all users');

//             Route::post('/store', 'store')
//                 ->middleware('permission:create user');

//             Route::match(['put', 'patch'], '/update/{user}', 'update')
//                 ->middleware('permission:edit user');

//             Route::delete('/delete/{user}', 'destroy')
//                 ->middleware('permission:delete user');
//         });

//     /* Clients */
//     Route::prefix('clients')
//         ->controller(\App\Http\Controllers\Api\ClientController::class)
//         ->group(function () {
//             Route::get('/index', 'index')
//                 ->middleware('permission:show clients');

//             Route::get('/record/{client}', 'show')
//                 ->middleware('permission:show clients');

//             Route::post('/store', 'store')
//                 ->middleware('permission:create client');

//             Route::post('/update/{client}', 'update')
//                 ->middleware('permission:edit client');

//             Route::delete('/delete/{client}', 'destroy')
//                 ->middleware('permission:delete client');
//         });

//     /* Categories */
//     Route::prefix('categories')
//         ->controller(\App\Http\Controllers\Api\CategoryController::class)
//         ->group(function () {
//             Route::get('/', 'index')
//                 ->middleware('permission:show categories');

//             Route::post('/store', 'store')
//                 ->middleware('permission:create category');

//             Route::post('/update/{category}', 'update')
//                 ->middleware('permission:edit category');

//             Route::delete('/delete/{category}', 'destroy')
//                 ->middleware('permission:delete category');
//         });

//     /* Items */
//     Route::prefix('items')
//         ->controller(ItemController::class)
//         ->group(function () {
//             Route::get('/', 'index')
//                 ->middleware('permission:show items');

//             Route::get('/allowed-fields', 'allowedFields')
//                 ->middleware('permission:show items|create item|edit item');

//             Route::post('/store', 'store')
//                 ->middleware('permission:create item');

//             Route::post('/update/{item}', 'update')
//                 ->middleware('permission:edit item');

//             Route::delete('/delete/{item}', 'destroy')
//                 ->middleware('permission:delete item');
//         });

//     /* Item barcode */
//     Route::prefix('items')->group(function () {
//         Route::get('/barcode/{barcode}', [ItemBarcodeController::class, 'lookup'])
//             ->middleware('permission:show items');

//         Route::post('/{item}/barcode/generate', [ItemBarcodeController::class, 'generate'])
//             ->middleware('permission:edit item');

//         Route::post('/store-with-barcode', [ItemBarcodeController::class, 'storeWithBarcode'])
//             ->middleware('permission:create item');

//         Route::get('/{item}/barcode/label', [ItemBarcodeController::class, 'label'])
//             ->middleware('permission:show items');
//     });

//     Route::post('/ai/scan', [\App\Http\Controllers\Api\ItemAiController::class, 'photoEntry'])
//         ->middleware('permission:create item|edit item');

//     Route::get('/business-types', [\App\Http\Controllers\Api\BusinessTypeController::class, 'index']);

//     /* Metal rates - database currently has only "show metal rates" permission */
//     Route::prefix('metal-rates')
//         ->controller(App\Http\Controllers\Api\MetalRateController::class)
//         ->middleware('permission:show metal rates')
//         ->group(function () {
//             Route::get('/', 'index');
//             Route::get('/current', 'currentRates');
//             Route::get('/latest', 'latestRate');
//             Route::get('/by-date', 'rateByDate');
//             Route::get('/show/{metalRate}', 'show');
//             Route::post('/today', 'storeToday');
//             Route::post('/store', 'store');
//             Route::match(['post', 'put', 'patch'], '/update/{metalRate}', 'update');
//             Route::match(['post', 'patch'], '/status/{metalRate}', 'toggle');
//             Route::delete('/delete/{metalRate}', 'destroy');
//         });

//     /* Invoices, proformas and quotations */
//     Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
//         Route::get('/index/tax', 'index')
//             ->defaults('type', 'tax')
//             ->middleware('permission:show invoices');

//         Route::get('/index/proforma', 'index')
//             ->defaults('type', 'proforma')
//             ->middleware('permission:show proformas');

//         Route::get('/index/quotation', 'index')
//             ->defaults('type', 'quotation')
//             ->middleware('permission:show quotations');

//         Route::post('/store/tax', 'store')
//             ->defaults('docType', 'tax')
//             ->middleware('permission:create invoice');

//         Route::post('/store/proforma', 'store')
//             ->defaults('docType', 'proforma')
//             ->middleware('permission:create proforma');

//         Route::post('/store/quotation', 'store')
//             ->defaults('docType', 'quotation')
//             ->middleware('permission:create quotation');

//         /*
//          | These ID-based routes need InvoicePolicy/controller authorization too,
//          | because the document type is known only after loading the invoice.
//          */
//         Route::get('/{invoice}', 'show')
//             ->middleware('permission:show invoices|show proformas|show quotations');

//         Route::post('/update/{invoice}', 'update')
//             ->middleware('permission:edit invoice|edit proforma|edit quotation');

//         Route::delete('/delete/{invoice}', 'destroy')
//             ->middleware('permission:delete invoice|delete proforma|delete quotation');

//         Route::get('/pdf/show/{invoice}', 'show')
//             ->middleware('permission:show invoices|show proformas|show quotations');

//         Route::get('/pdf-url/{invoice}/pdf-url', 'pdfUrl')
//             ->middleware('permission:show invoices|show proformas|show quotations');

//         Route::post('/invoice-number/{type?}', 'preview')
//             ->middleware('permission:create invoice|create proforma|create quotation');

//         Route::post('/{invoice}/convert-to-tax', 'convertToTax')
//             ->middleware('permission:convert into tax invoice');
//     });

//     /* Invoice sending */
//     Route::prefix('invoice-send')
//         ->controller(\App\Http\Controllers\Api\InvoiceSendReportController::class)
//         ->group(function () {
//             Route::get('/reports', 'index')
//                 ->middleware('permission:show invoice sends');

//             Route::post('/upload', 'uploadAndSend')
//                 ->middleware('permission:show invoice sends');
//         });

//     Route::post('/whatsapp/upload-send-pdf', [InvoiceSendController::class, 'uploadAndSendPdf'])
//         ->middleware('permission:show invoice sends');

//     /* External API keys */
//     Route::prefix('api-keys')
//         ->controller(\App\Http\Controllers\Api\ApiKeyController::class)
//         ->middleware('permission:show wishes api field')
//         ->group(function () {
//             Route::get('/index', 'index');
//             Route::post('/store', 'store');
//             Route::delete('/delete/{api}', 'destroy');
//         });

//     /* Birthday and anniversary */
//     Route::prefix('birthday-records')
//         ->controller(\App\Http\Controllers\Api\BirthdayRecordController::class)
//         ->middleware('permission:show birthday records')
//         ->group(function () {
//             Route::get('/', 'index');
//             Route::post('/store', 'store');
//             Route::post('/update/{birthdayRecord}', 'update');
//             Route::delete('/delete/{birthdayRecord}', 'destroy');
//             Route::post('/import', 'import');
//         });

//     Route::prefix('wishes-logs')
//         ->controller(\App\Http\Controllers\Api\BirthdayWishLogController::class)
//         ->middleware('permission:show wishes logs')
//         ->group(function () {
//             Route::get('/', 'index');
//         });

//     Route::get('/anniversary-records', [\App\Http\Controllers\Api\AnniversaryController::class, 'index'])
//         ->middleware('permission:show anniversary records');

//     /* Bank accounts */
//     Route::prefix('bank-accounts')
//         ->controller(\App\Http\Controllers\Api\BankAccountController::class)
//         ->middleware('permission:show bank balance')
//         ->group(function () {
//             Route::get('/', 'index');
//             Route::get('/show/{bank}', 'show');
//             Route::post('/store', 'store');
//             Route::post('/update/{bank}', 'update');
//             Route::delete('/delete/{bank}', 'destroy');
//         });

//     /* Installment reminders */
//     Route::prefix('installment-reminders')
//         ->middleware('permission:show installment reminders')
//         ->group(function () {
//             Route::get('/', [InstallmentReminderController::class, 'index']);
//             Route::post('/store', [InstallmentReminderController::class, 'store']);
//             Route::get('/show/{reminder}', [InstallmentReminderController::class, 'show']);
//             Route::match(['put', 'patch'], '/update/{reminder}', [InstallmentReminderController::class, 'update']);
//             Route::delete('/delete/{reminder}', [InstallmentReminderController::class, 'destroy']);
//             Route::patch('/status/{reminder}/status', [InstallmentReminderController::class, 'statusUpdate']);
//             Route::post('/import', [InstallmentReminderController::class, 'import']);
//         });

//     /* Bill templates */
//     Route::get('/bill-templates', [BillTemplateController::class, 'apiChoose'])
//         ->middleware('permission:show bill templates|choose templates');

//     Route::post('/bill-templates/choose', [BillTemplateController::class, 'apiSaveChosen'])
//         ->middleware('permission:choose templates');

//     /* Bill requests */
//     Route::prefix('bill-requests')
//         ->middleware('permission:show bill requests')
//         ->group(function () {
//             Route::get('/', [ApiBillRequestController::class, 'index']);
//             Route::get('/show/{billRequest}', [ApiBillRequestController::class, 'show']);
//             Route::post('/create-invoice/{billRequest}', [ApiBillRequestController::class, 'createInvoice']);
//             Route::get('/invoice/{billRequest}', [ApiBillRequestController::class, 'showInvoice']);
//             Route::delete('/destroy/{billRequest}', [ApiBillRequestController::class, 'destroy']);
//         });

//     /* Purchases */
//     Route::prefix('purchases')
//         ->middleware('permission:show purchases')
//         ->group(function () {
//             Route::get('/form-data', [PurchaseController::class, 'formData']);
//             Route::get('/', [PurchaseController::class, 'index']);
//             Route::post('/', [PurchaseController::class, 'store']);
//             Route::get('/{purchase}', [PurchaseController::class, 'show']);
//             Route::match(['put', 'patch'], '/{purchase}', [PurchaseController::class, 'update']);
//             Route::delete('/{purchase}', [PurchaseController::class, 'destroy']);
//         });

//     /* Plans */
//     Route::prefix('plans')->group(function () {
//         Route::get('/', [PlanController::class, 'index'])
//             ->middleware('permission:show plan');

//         Route::post('/', [PlanController::class, 'store'])
//             ->middleware('permission:show plan');

//         Route::get('/{id}', [PlanController::class, 'show'])
//             ->middleware('permission:show plan');

//         Route::match(['put', 'patch'], '/{id}', [PlanController::class, 'update'])
//             ->middleware('permission:show plan');

//         Route::delete('/{id}', [PlanController::class, 'destroy'])
//             ->middleware('permission:show plan');

//         Route::patch('/{id}/toggle-status', [PlanController::class, 'toggleStatus'])
//             ->middleware('permission:show plan');
//     });

//     Route::get('/my-plans', [PlanController::class, 'myPlans']);

//     Route::get('/choose-plans', [PlanController::class, 'choose']);

//     Route::post('/choose-plan-save', [PlanController::class, 'choosenSave']);

//     Route::get('/my-active-plan', [PlanPaymentController::class, 'myActivePlan']);

//     Route::get('/banner-sliders', [BannerSliderController::class, 'index']);

//     /* Units - mapped to item permissions because separate unit permissions do not exist */
//     Route::prefix('units')->group(function () {
//         Route::get('/', [UnitController::class, 'index'])
//             ->middleware('permission:show items|create item|edit item');

//         Route::post('/', [UnitController::class, 'store'])
//             ->middleware('permission:create item');

//         Route::post('/quick-store', [UnitController::class, 'quickStore'])
//             ->middleware('permission:create item');

//         Route::get('/{id}', [UnitController::class, 'show'])
//             ->middleware('permission:show items|edit item');

//         Route::match(['put', 'patch'], '/{id}', [UnitController::class, 'update'])
//             ->middleware('permission:edit item');

//         Route::delete('/{id}', [UnitController::class, 'destroy'])
//             ->middleware('permission:delete item');
//     });
// });

// /*
// | User-specific endpoints only need authentication. They expose the current
// | authenticated user's own information.
// */
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', [HomeController::class, 'user']);
//     Route::post('/user/profile-update', [HomeController::class, 'updateProfile']);
//     Route::post('/user/change-password', [HomeController::class, 'changePassword']);
//     Route::get('/dashboard', [HomeController::class, 'index']);
//     Route::get('/user/permissions', [HomeController::class, 'myPermissions']);
//     Route::get('user/permissions1', [HomeController::class, 'myPermissions1']);

// });




use App\Http\Controllers\Api\BannerSliderController;
use App\Http\Controllers\Api\BillRequestController as ApiBillRequestController;
use App\Http\Controllers\Api\BillTemplateController;
use App\Http\Controllers\Api\BirthdayWishController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\InstallmentReminderController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceSendController;
use App\Http\Controllers\Api\ItemBarcodeController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\HospitalManagementController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PlanPaymentController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [HomeController::class, 'login']);
Route::post('/login/verify-otp', [HomeController::class, 'verifyLoginOtp']);
Route::post('/register', [HomeController::class, 'register']);
Route::post('/register/verify-otp', [HomeController::class, 'verifyRegisterOtp']);

Route::post('/plans/{plan}/create-order', [PlanPaymentController::class, 'createOrder']);
Route::post('/plans/payment/verify', [PlanPaymentController::class, 'verifyPayment']);

Route::post('/request-for-bill', [ApiBillRequestController::class, 'store']);
Route::get('/bill-requests/index/api', [ApiBillRequestController::class, 'index1']);
Route::get('/items1', [ItemController::class, 'index1']);

/*
| These scheduler routes should ideally be protected by a secret-token
| middleware or moved to console scheduler commands.
*/
Route::get('/birthday-wishes/run', [BirthdayWishController::class, 'run']);
Route::get('/installment-reminders/run', [\App\Http\Controllers\InstallmentReminderController::class, 'run']);
Route::get('/drive-scan-pdf/run', [\App\Http\Controllers\InstallmentReminderController::class, 'driveScanPdf']);
Route::get('/send-uploaded-invoice/run', [\App\Http\Controllers\InstallmentReminderController::class, 'sendUploadedInvoice']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Spatie middleware syntax:
|   permission:permission name
|   permission:first permission|second permission  => OR condition
|
*/
Route::middleware(['auth:sanctum', 'active.business'])->group(function () {

    Route::post('/logout', [HomeController::class, 'logout']);

    Route::post('/delete-account/send-otp', [HomeController::class, 'sendDeleteAccountOtp'])
        ->middleware('throttle:3,10');

    Route::post('/delete-account', [HomeController::class, 'deleteAccount'])
        ->middleware('throttle:5,10');

    /* Business */
    Route::prefix('businesses')->group(function () {
        Route::get('/index', [BusinessController::class, 'index']);

        Route::post('/store', [BusinessController::class, 'store']);

        Route::post('/update/{business}', [BusinessController::class, 'update']);

        Route::delete('/delete/{business}', [BusinessController::class, 'destroy'])
            ->middleware('permission:delete business');
    });

    /* Users */
    Route::prefix('users')
        ->controller(\App\Http\Controllers\Api\UserController::class)
        ->group(function () {
            Route::get('/index', 'index')
                ->middleware('permission:show users|view all users');

            Route::post('/store', 'store')
                ->middleware('permission:create user');

            Route::match(['put', 'patch'], '/update/{user}', 'update')
                ->middleware('permission:edit user');

            Route::delete('/delete/{user}', 'destroy')
                ->middleware('permission:delete user');
        });

    /* Clients */
    Route::prefix('clients')
        ->controller(\App\Http\Controllers\Api\ClientController::class)
        ->group(function () {
            Route::get('/index', 'index')
                ->middleware('permission:show clients');

            Route::get('/record/{client}', 'show')
                ->middleware('permission:show clients');

            Route::post('/store', 'store')
                ->middleware('permission:create client');

            Route::post('/update/{client}', 'update')
                ->middleware('permission:edit client');

            Route::delete('/delete/{client}', 'destroy')
                ->middleware('permission:delete client');
        });

    /* Categories */
    Route::prefix('categories')
        ->controller(\App\Http\Controllers\Api\CategoryController::class)
        ->group(function () {
            Route::get('/', 'index')
                ->middleware('permission:show categories');

            Route::post('/store', 'store');

            Route::post('/update/{category}', 'update')
                ->middleware('permission:edit category');

            Route::delete('/delete/{category}', 'destroy')
                ->middleware('permission:delete category');
        });

    /* Items */
    Route::prefix('items')
        ->controller(ItemController::class)
        ->group(function () {
            Route::get('/', 'index')
                ->middleware('permission:show items');

            Route::get('/allowed-fields', 'allowedFields')
                ->middleware('permission:show items|create item|edit item');

            Route::post('/store', 'store')
                ->middleware('permission:create item');

            Route::post('/update/{item}', 'update')
                ->middleware('permission:edit item');

            Route::delete('/delete/{item}', 'destroy')
                ->middleware('permission:delete item');
        });

    /* Item barcode */
    Route::prefix('items')->group(function () {
        Route::get('/barcode/{barcode}', [ItemBarcodeController::class, 'lookup'])
            ->middleware('permission:show items');

        Route::post('/{item}/barcode/generate', [ItemBarcodeController::class, 'generate'])
            ->middleware('permission:edit item');

        Route::post('/store-with-barcode', [ItemBarcodeController::class, 'storeWithBarcode'])
            ->middleware('permission:create item');

        Route::get('/{item}/barcode/label', [ItemBarcodeController::class, 'label'])
            ->middleware('permission:show items');
    });

    Route::post('/ai/scan', [\App\Http\Controllers\Api\ItemAiController::class, 'photoEntry'])
        ->middleware('permission:create item|edit item');

    Route::get('/business-types', [\App\Http\Controllers\Api\BusinessTypeController::class, 'index']);

    /* Metal rates - database currently has only "show metal rates" permission */
    Route::prefix('metal-rates')
        ->controller(App\Http\Controllers\Api\MetalRateController::class)
        ->middleware('permission:show metal rates')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/current', 'currentRates');
            Route::get('/latest', 'latestRate');
            Route::get('/by-date', 'rateByDate');
            Route::get('/show/{metalRate}', 'show');
            Route::post('/today', 'storeToday');
            Route::post('/store', 'store');
            Route::match(['post', 'put', 'patch'], '/update/{metalRate}', 'update');
            Route::match(['post', 'patch'], '/status/{metalRate}', 'toggle');
            Route::delete('/delete/{metalRate}', 'destroy');
        });

    /* Invoices, proformas and quotations */
    Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
        Route::get('/index/tax', 'index')
            ->defaults('type', 'tax')
            ->middleware('permission:show invoices');

        Route::get('/index/proforma', 'index')
            ->defaults('type', 'proforma')
            ->middleware('permission:show proformas');

        Route::get('/index/quotation', 'index')
            ->defaults('type', 'quotation')
            ->middleware('permission:show quotations');

        Route::post('/store/tax', 'store')
            ->defaults('docType', 'tax')
            ->middleware('permission:create invoice');

        Route::post('/store/proforma', 'store')
            ->defaults('docType', 'proforma')
            ->middleware('permission:create proforma');

        Route::post('/store/quotation', 'store')
            ->defaults('docType', 'quotation')
            ->middleware('permission:create quotation');

        /*
         | These ID-based routes need InvoicePolicy/controller authorization too,
         | because the document type is known only after loading the invoice.
         */
        Route::get('/{invoice}', 'show')
            ->middleware('permission:show invoices|show proformas|show quotations');

        Route::post('/update/{invoice}', 'update')
            ->middleware('permission:edit invoice|edit proforma|edit quotation');

        Route::delete('/delete/{invoice}', 'destroy')
            ->middleware('permission:delete invoice|delete proforma|delete quotation');

        Route::get('/pdf/show/{invoice}', 'show')
            ->middleware('permission:show invoices|show proformas|show quotations');

        Route::get('/pdf-url/{invoice}/pdf-url', 'pdfUrl')
            ->middleware('permission:show invoices|show proformas|show quotations');

        Route::post('/invoice-number/{type?}', 'preview')
            ->middleware('permission:create invoice|create proforma|create quotation');

        Route::post('/{invoice}/convert-to-tax', 'convertToTax')
            ->middleware('permission:convert into tax invoice');
    });

    /* Invoice sending */
    Route::prefix('invoice-send')
        ->controller(\App\Http\Controllers\Api\InvoiceSendReportController::class)
        ->group(function () {
            Route::get('/reports', 'index')
                ->middleware('permission:show invoice sends');

            Route::post('/upload', 'uploadAndSend')
                ->middleware('permission:show invoice sends');
        });

    Route::post('/whatsapp/upload-send-pdf', [InvoiceSendController::class, 'uploadAndSendPdf'])
        ->middleware('permission:show invoice sends');

    /* External API keys */
    Route::prefix('api-keys')
        ->controller(\App\Http\Controllers\Api\ApiKeyController::class)
        ->middleware('permission:show wishes api field')
        ->group(function () {
            Route::get('/index', 'index');
            Route::post('/store', 'store');
            Route::delete('/delete/{api}', 'destroy');
        });

    /* Birthday and anniversary */
    Route::prefix('birthday-records')
        ->controller(\App\Http\Controllers\Api\BirthdayRecordController::class)
        ->middleware('permission:show birthday records')
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/store', 'store');
            Route::post('/update/{birthdayRecord}', 'update');
            Route::delete('/delete/{birthdayRecord}', 'destroy');
            Route::post('/import', 'import');
        });

    Route::prefix('wishes-logs')
        ->controller(\App\Http\Controllers\Api\BirthdayWishLogController::class)
        ->middleware('permission:show wishes logs')
        ->group(function () {
            Route::get('/', 'index');
        });

    Route::get('/anniversary-records', [\App\Http\Controllers\Api\AnniversaryController::class, 'index'])
        ->middleware('permission:show anniversary records');

    /* Bank accounts */
    Route::prefix('bank-accounts')
        ->controller(\App\Http\Controllers\Api\BankAccountController::class)
        ->middleware('permission:show bank balance')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/show/{bank}', 'show');
            Route::post('/store', 'store');
            Route::post('/update/{bank}', 'update');
            Route::delete('/delete/{bank}', 'destroy');
        });

    /* Installment reminders */
    Route::prefix('installment-reminders')
        ->middleware('permission:show installment reminders')
        ->group(function () {
            Route::get('/', [InstallmentReminderController::class, 'index']);
            Route::post('/store', [InstallmentReminderController::class, 'store']);
            Route::get('/show/{reminder}', [InstallmentReminderController::class, 'show']);
            Route::match(['put', 'patch'], '/update/{reminder}', [InstallmentReminderController::class, 'update']);
            Route::delete('/delete/{reminder}', [InstallmentReminderController::class, 'destroy']);
            Route::patch('/status/{reminder}/status', [InstallmentReminderController::class, 'statusUpdate']);
            Route::post('/import', [InstallmentReminderController::class, 'import']);
        });

    /* Bill templates */
    Route::get('/bill-templates', [BillTemplateController::class, 'apiChoose'])
        ->middleware('permission:show bill templates|choose templates');

    Route::post('/bill-templates/choose', [BillTemplateController::class, 'apiSaveChosen'])
        ->middleware('permission:choose templates');

    /* Bill requests */
    Route::prefix('bill-requests')
        ->middleware('permission:show bill requests')
        ->group(function () {
            Route::get('/', [ApiBillRequestController::class, 'index']);
            Route::get('/show/{billRequest}', [ApiBillRequestController::class, 'show']);
            Route::post('/create-invoice/{billRequest}', [ApiBillRequestController::class, 'createInvoice']);
            Route::get('/invoice/{billRequest}', [ApiBillRequestController::class, 'showInvoice']);
            Route::delete('/destroy/{billRequest}', [ApiBillRequestController::class, 'destroy']);
        });

    /* Purchases */
    Route::prefix('purchases')
        ->middleware('permission:show purchases')
        ->group(function () {
            Route::get('/form-data', [PurchaseController::class, 'formData']);
            Route::get('/', [PurchaseController::class, 'index']);
            Route::post('/', [PurchaseController::class, 'store']);
            Route::get('/{purchase}', [PurchaseController::class, 'show']);
            Route::match(['put', 'patch'], '/{purchase}', [PurchaseController::class, 'update']);
            Route::delete('/{purchase}', [PurchaseController::class, 'destroy']);
        });

    /* Plans */
    Route::prefix('plans')->group(function () {
        Route::get('/', [PlanController::class, 'index']);

        Route::post('/', [PlanController::class, 'store'])
            ->middleware('permission:show plan');

        Route::get('/{id}', [PlanController::class, 'show'])
            ->middleware('permission:show plan');

        Route::match(['put', 'patch'], '/{id}', [PlanController::class, 'update'])
            ->middleware('permission:show plan');

        Route::delete('/{id}', [PlanController::class, 'destroy'])
            ->middleware('permission:show plan');

        Route::patch('/{id}/toggle-status', [PlanController::class, 'toggleStatus'])
            ->middleware('permission:show plan');
    });

    Route::get('/my-plans', [PlanController::class, 'myPlans']);

    Route::get('/choose-plans', [PlanController::class, 'choose']);

    Route::post('/choose-plan-save', [PlanController::class, 'choosenSave']);

    Route::get('/my-active-plan', [PlanPaymentController::class, 'myActivePlan']);

    Route::get('/banner-sliders', [BannerSliderController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | Hospital Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('hospital')
        ->controller(HospitalManagementController::class)
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get('/dashboard', 'dashboard');


            /*
            |--------------------------------------------------------------------------
            | Doctors
            |--------------------------------------------------------------------------
            */

            Route::get('/doctors', 'doctors');

            Route::post('/doctors', 'storeDoctor');

            Route::match(
                ['put', 'patch'],
                '/doctors/{doctor}',
                'updateDoctor'
            );

            Route::delete(
                '/doctors/{doctor}',
                'deleteDoctor'
            );


            /*
            |--------------------------------------------------------------------------
            | Patients
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/patients',
                'patients'
            );

            Route::post(
                '/patients',
                'storePatient'
            );

            Route::match(
                ['put', 'patch'],
                '/patients/{patient}',
                'updatePatient'
            );


            /*
            |--------------------------------------------------------------------------
            | Departments
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/departments',
                'departments'
            );

            Route::post(
                '/departments',
                'storeDepartment'
            );

            Route::match(
                ['put', 'patch'],
                '/departments/{department}',
                'updateDepartment'
            );

            Route::delete(
                '/departments/{department}',
                'deleteDepartment'
            );


            /*
            |--------------------------------------------------------------------------
            | Wards
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/wards',
                'wards'
            );

            Route::post(
                '/wards',
                'storeWard'
            );

            Route::match(
                ['put', 'patch'],
                '/wards/{ward}',
                'updateWard'
            );

            Route::delete(
                '/wards/{ward}',
                'deleteWard'
            );


            /*
            |--------------------------------------------------------------------------
            | Rooms
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/rooms',
                'rooms'
            );

            Route::post(
                '/rooms',
                'storeRoom'
            );

            Route::match(
                ['put', 'patch'],
                '/rooms/{room}',
                'updateRoom'
            );

            Route::delete(
                '/rooms/{room}',
                'deleteRoom'
            );


            /*
            |--------------------------------------------------------------------------
            | Beds
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/beds',
                'beds'
            );

            Route::post(
                '/beds',
                'storeBed'
            );

            Route::match(
                ['put', 'patch'],
                '/beds/{bed}',
                'updateBed'
            );

            Route::delete(
                '/beds/{bed}',
                'deleteBed'
            );


            /*
            |--------------------------------------------------------------------------
            | Visits
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/visits',
                'visits'
            );
        });


    /*
    |--------------------------------------------------------------------------
    | Units
    |--------------------------------------------------------------------------
    */

    Route::prefix('units')->group(function () {

        Route::get('/', [UnitController::class, 'index'])
            ->middleware(
                'permission:show items|create item|edit item'
            );

        Route::post('/', [UnitController::class, 'store'])
            ->middleware('permission:create item');

        Route::post(
            '/quick-store',
            [UnitController::class, 'quickStore']
        )->middleware('permission:create item');

        Route::get(
            '/{id}',
            [UnitController::class, 'show']
        )->middleware(
            'permission:show items|edit item'
        );

        Route::match(
            ['put', 'patch'],
            '/{id}',
            [UnitController::class, 'update']
        )->middleware('permission:edit item');

        Route::delete(
            '/{id}',
            [UnitController::class, 'destroy']
        )->middleware('permission:delete item');
    });
});

/*
| User-specific endpoints only need authentication. They expose the current
| authenticated user's own information.
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [HomeController::class, 'user']);
    Route::post('/user/profile-update', [HomeController::class, 'updateProfile']);
    Route::post('/user/change-password', [HomeController::class, 'changePassword']);
    Route::get('/dashboard', [HomeController::class, 'index']);
    Route::get('/user/permissions', [HomeController::class, 'myPermissions']);
    Route::get('user/permissions1', [HomeController::class, 'myPermissions1']);

});