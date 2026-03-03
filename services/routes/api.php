<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\PayrollApiController;
use App\Http\Controllers\RosterApiController;
use App\Http\Controllers\ProfileApiController;
use App\Http\Controllers\SalaryApiController;
use App\Http\Middleware\CheckKeycloakToken;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([CheckKeycloakToken::class])->group(function() {
    Route::get('/employees', [RosterApiController::class, 'getRoster']);
    Route::get('/payrolls', [PayrollApiController::class, 'getPayroll']);

    Route::post('/employees', [RosterApiController::class, 'storeEmployee']);
    Route::get('/employees/{id}', [RosterApiController::class, 'getEmployee']);
    Route::put('/employees/{id}', [RosterApiController::class, 'updateEmployee']);
    Route::delete('/employees/{id}', [RosterApiController::class, 'destroyEmployee']);

    Route::get('/salary', [SalaryApiController::class, 'getSalary']);
    Route::get('/profile', [ProfileApiController::class, 'getProfile']);

    Route::get('dashboard/manager-data', [DashboardApiController::class, 'getManagerDashboard']);
    Route::get('dashboard/employee-data', [DashboardApiController::class, 'getEmployeeDashboard']);
});