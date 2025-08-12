<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateLoanRequest;
use App\Models\Loan;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class LoanController extends Controller
{
    //
    public function generate(GenerateLoanRequest $request)
    {
        $validated = $request->validated();

        $installmentsPerLoan = $validated['installments_per_loan'] ?? 4; // default val
        $installmentPeriod = $validated['installment_period_minutes'] ?? 60; // default val

        DB::transaction(function () use ($validated, $installmentsPerLoan, $installmentPeriod) {
            for ($i = 0; $i < $validated['number_of_loans']; $i++) {
                $loan = Loan::create([
                    'amount' => $validated['loan_amount'],
                    'status' => 'active',
                ]);

                $installmentAmount = $loan->amount / $installmentsPerLoan;

                for ($j = 0; $j < $installmentsPerLoan; $j++) {
                    Installment::create([
                        'loan_id' => $loan->id,
                        'amount' => $installmentAmount,
                        'due_date' => Carbon::now()->addMinutes($installmentPeriod * ($j + 1)),
                        'status' => 'pending'
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Loans generated successfully'], 201);
    }
}
