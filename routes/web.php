<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WashController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');



Route::middleware('auth')->group(function () {
    Route::get('/', [WashController::class, 'index'])->name('washes');
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/list', [CompanyController::class, 'list'])->name('companies.list');



    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/list', [VehicleController::class, 'list'])->name('vehicles.list');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');


    Route::get('/washes', [WashController::class, 'index'])->name('washes');
    Route::post('/washes/lookup', [WashController::class, 'lookupVehicle'])->name('washes.lookup');
    Route::post('/washes/record', [WashController::class, 'recordWash'])->name('washes.record');
    Route::post('/washes/cash', [WashController::class, 'recordCashWash'])->name('washes.cash');




    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // routes/web.php
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');

     Route::get('/import', [ImportController::class, 'showForm'])->name('import.form');
    Route::post('/import', [ImportController::class, 'import'])->name('import.process');

    Route::get('/import/sample', function () {
    $csv = "registration,driver_name,company_name,price,limit\n";
    $csv .= "ABC123,John Doe,XYZ Logistics,12.50,1\n";

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename=sample.csv');
})->name('import.sample');

});