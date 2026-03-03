<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileApiController;
use App\Http\Controllers\SalaryApiController;
use App\Http\Controllers\DashboardApiController; 

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('manager')->group(function () {
    Route::get('/dashboard', function () { return view('manager.dashboard'); });
    Route::get('/roster', function () { return view('manager.roster'); });
    Route::get('/payroll', function () { return view('manager.payroll'); });
    Route::get('/profile', function () { return view('profile'); });
});

Route::prefix('employee')->group(function () {
    Route::get('/dashboard', function () { return view('employee.dashboard'); });
    Route::get('/salary', function () { return view('employee.salary'); });
    Route::get('/profile', function () { return view('profile'); });
});