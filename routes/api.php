<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;

Route::post('/loans/generate', [LoanController::class, 'generate']);
