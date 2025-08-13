<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;


Route::post('/loans/generate', [LoanController::class, 'generate']);
Route::post('/installments/{installment}/pay', [PaymentController::class, 'pay']);
Route::patch('/loans/{loan}/status', [LoanController::class, 'updateStatus']);