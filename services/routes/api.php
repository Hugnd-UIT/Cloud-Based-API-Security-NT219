<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileApiController;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\RosterApiController;
use App\Http\Controllers\PayrollApiController;
use App\Http\Controllers\SalaryApiController;
use App\Http\Middleware\VerifyKeycloakToken;
use App\Http\Middleware\VerifyTokenRevocation;
use App\Http\Controllers\VNPayController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([VerifyKeycloakToken::class, VerifyTokenRevocation::class])->group(function() {
    Route::get('/salary', [SalaryApiController::class, 'getSalary']);
    Route::get('/profile', [ProfileApiController::class, 'getProfile']);

    Route::get('/employees', [RosterApiController::class, 'getRoster']);
    Route::get('/payrolls', [PayrollApiController::class, 'getPayroll']);

    Route::post('/employees', [RosterApiController::class, 'storeEmployee']);
    Route::get('/employees/{id}', [RosterApiController::class, 'getEmployee']);
    Route::put('/employees/{id}', [RosterApiController::class, 'updateEmployee']);
    Route::delete('/employees/{id}', [RosterApiController::class, 'destroyEmployee']);

    Route::get('dashboard/manager-data', [DashboardApiController::class, 'getManagerDashboard']);
    Route::get('dashboard/employee-data', [DashboardApiController::class, 'getEmployeeDashboard']);

    Route::post('/vnpay/create', [VNPayController::class, 'createPayment']);
});

Route::get('/vnpay-ipn', [VNPayController::class, 'vnpayIpn']);