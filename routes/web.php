<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::view('/loan-form', 'loan-form');

Route::get('/loan-form', function () {
    return view('loan-form');
})->name('loan.form');


