<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;


Route::post('/loans/generate', [LoanController::class, 'generate']);
Route::post('/installments/{installment}/pay', [PaymentController::class, 'pay']);
Route::patch('/loans/{loan}/status', [LoanController::class, 'updateStatus']);
Route::get('/loans/payments', [LoanController::class, 'getPayments']);
Route::get('/loans/status-counts', [LoanController::class, 'getLoanStatusCounts']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers', [CustomerController::class, 'index']);
Route::patch('/customers/{id}', [CustomerController::class, 'update']);