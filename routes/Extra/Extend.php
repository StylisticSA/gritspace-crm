<?php

use App\Http\Controllers\AgrementUploadController;
use App\Http\Controllers\BankingDetailController;
use App\Http\Controllers\BoardroomController;
use App\Http\Controllers\ClosedOfficeController;
use App\Http\Controllers\ClosedOfficeRateController;
use App\Http\Controllers\CoffeeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DedicatedDeskController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\HelpDeskController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\PrintingController;


Route::middleware(['web', 'auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('closed-office-rates', ClosedOfficeRateController::class)->names([
                        'index' => 'closedrate.index',
                        'create' => 'closedrate.create',
                        'store' => 'closedrate.store',
                        'show' => 'closedrate.show',
                        'edit' => 'closedrate.edit',
                        'update' => 'closedrate.update',
                        'destroy' => 'closedrate.destroy',
                ]);


        Route::resource('notes', NoteController::class)->names([
                        'index' => 'notes.index',
                        'create' => 'notes.create',
                        'store' => 'notes.store',
                        'show' => 'notes.show',
                        'edit' => 'notes.edit',
                        'update' => 'notes.update',
                        'destroy' => 'notes.destroy',
                ]);

        Route::resource('company', CompanyController::class)->names([
                'index' => 'company.index',
                'create' => 'company.create',
                'store' => 'company.store',
                'show' => 'company.show',
                'edit' => 'company.edit',
                'update' => 'company.update',
                'destroy' => 'company.destroy',
        ]);


        Route::resource('invoices', InvoiceController::class)->names([
                'index' => 'invoices.index',
                'create' => 'invoices.create',
                'store' => 'invoices.store',
                'show' => 'invoices.show',
                'edit' => 'invoices.edit',
                'update' => 'invoices.update',
                'destroy' => 'invoices.destroy',
        ]);

        Route::resource('discounts', DiscountController::class)->names([
                'index' => 'discounts.index',
                'create' => 'discount.create',
                'store' => 'discounts.store',
                'show' => 'discount.show',
                'edit' => 'discount.edit',
                'update' => 'discount.update',
                'destroy' => 'discount.destroy',
        ]);

        Route::post('/invoice/send-invoice', [InvoiceController::class, 'sendInvoice'])->name('invoice.send-invoice');
        Route::put('/invoice/{invoice}/paid', [InvoiceController::class, 'paid'])->name('invoice.paid');
        Route::put('/invoice/{invoice}/pending', [InvoiceController::class, 'pending'])->name('invoice.pending');
        Route::put('/invoice/{invoice}/cancel', [InvoiceController::class, 'cancelled'])->name('invoice.cancelled');
        Route::get('/invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');


        Route::resource('banking', BankingDetailController::class)->names([
                'index' => 'banking.index',
                'create' => 'banking.create',
                'store' => 'banking.store',
                'show' => 'banking.show',
                'edit' => 'banking.edit',
                'update' => 'banking.update',
                'destroy' => 'banking.destroy',
        ]);

        Route::resource('coffee-admin', CoffeeController::class)->names([
                        'index' => 'coffee.index',
                        'create' => 'coffee.create',
                        'edit' => 'coffee.edit',
                        'update' => 'coffee.update',
                        'destroy' => 'coffee.destroy',
                ]);


        Route::resource('printing-admin', PrintingController::class)->names([
                        'index' => 'printing.index',
                        'create' => 'printing.create',
                        'edit' => 'printing.edit',
                        'update' => 'printing.update',
                        'destroy' => 'printing.destroy',
                ]);


        Route::resource("extras-settings", ExtraController::class)->names([
                        'index' => 'extra.index',
                        'create' => 'extra.create',
                        'store' => 'extra.store',
                        'edit' => 'extra.edit',
                        'update' => 'extra.update',
                        'destroy' => 'extra.destroy',
                ]);


        Route::resource("parking", ParkingController::class)->names([
                                'index' => 'parking.index',
                                'create' => 'parking.create',
                                'store' => 'parking.store',
                                'edit' => 'parking.edit',
                                'update' => 'parking.update',
                                'destroy' => 'parking.destroy',
                        ]);


        Route::put('/parking/availability/{parking}', [ParkingController::class, 'availability'])->name('parking.availability');


        Route::resource('agreements', AgrementUploadController::class)->names([
                               'index'          => 'agreement.index',
                               'create'         => 'agreement.create',
                               'edit'           => 'agreement.edit',
                               'update'         => 'agreement.update',
                               'destroy'        => 'agreement.destroy',

                          ]);

        Route::put('/approve-agreement/{agreement}/approve', [AgrementUploadController::class, 'approve'])->name('agreement.approve');
        Route::put('/approve-agreement/{agreement}/pending', [AgrementUploadController::class, 'pending'])->name('agreement.pending');

        Route::put('/availability/closed/{closed}', [ClosedOfficeController::class, 'availability'])->name('closed.availability');
        Route::put('/availability/dedicated/{dedicated}', [DedicatedDeskController::class, 'availability'])->name('dedicated.availability');
        Route::put('/availability/hotdesk/{hotdesk}', [HelpDeskController::class, 'availability'])->name('hotdesk.availability');
        Route::put('/availability/boardroom/{boardroom}', [BoardroomController::class, 'availability'])->name('boardroom.availability');

    });

Route::middleware('auth')->group(function () {

    Route::resource('coffee', CoffeeController::class)->names([
                   'store' => 'coffee.store',
           ]);

    Route::resource('printing', PrintingController::class)->names([
                       'store' => 'printing.store',
               ]);

    Route::resource('agreement-upload', AgrementUploadController::class)->names([
                        'store' => 'agreement.store',
                   ]);


    Route::post('/activity-ping', function () {
        \Log::info('User activity ping', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'agent' => request()->userAgent(),
        ]);
        return response()->noContent();
    })->middleware('auth:sanctum');


    Route::post('/receive/cart', [PaymentGatewayController::class, 'receiveCart']);
    Route::get('/receive/cart', [PaymentGatewayController::class, 'receiveCart']);

    // Payments
    Route::post('/payment/initiate', [PaymentGatewayController::class, 'initiateTransaction'])->name('payment.initiate');
    Route::get('/payment/callback', [PaymentGatewayController::class, 'paymentCallback'])->name('payment.callback');


    Route::get('/checkout/success', function () {
        return view('checkout.success');
    })->name('checkout.success');

    Route::get('/checkout/failed', function () {
        return view('checkout.failed');
    })->name('checkout.failed');


    route::get('user/invoices', [InvoiceController::class, 'userIndex'])->name('user.invoice');
    route::get('user/invoices/{invoice}', [InvoiceController::class,'userShow'])->name('userView.invoice');

});
