<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateLoanRequest;
use App\Models\Loan;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Events\LoanGenerated;
use App\Http\Requests\UpdateLoanStatusRequest;
use App\Events\LoanStatusUpdated;


class LoanController extends Controller
{
    public function generate(GenerateLoanRequest $request)
    {
         try {
        $validated = $request->validated();

        $installmentsPerLoan = $validated['installments_per_loan'] ?? 4;
        $installmentPeriod = $validated['installment_period_minutes'] ?? 60;

        $loans = [];

        DB::transaction(function () use ($validated, $installmentsPerLoan, $installmentPeriod, &$loans) {
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

                // Push into collection for later event firing
                $loans[] = $loan->load('installments');
            }
        });

        // Fire an event for each generated loan
        foreach ($loans as $loan) {
            event(new LoanGenerated($loan));
        }

        return response()->json([
            'message' => count($loans) . ' loan(s) generated successfully',
            'loans'   => $loans
        ], 201);
    } catch (\Throwable $e) {
        \Log::error('Loan generation failed: ' . $e->getMessage());
        return response()->json(['error' => 'Loan generation failed'], 500);
    }
}

    // public function updateStatus(UpdateLoanStatusRequest $request, $id)
    // {
    //     $loan = Loan::findOrFail($id);
    //     $loan->status = $request->status;
    //     $loan->save();

    //     // Broadcast event
    //     event(new LoanStatusUpdated($loan));

    //     return response()->json([
    //         'message' => 'Loan status updated',
    //         'loan' => $loan->load('installments')
    //     ]);
    // }
    public function updateStatus(UpdateLoanStatusRequest $request, $id)
    {
        try {
            $loan = Loan::findOrFail($id);
            $loan->status = $request->status;
            $loan->save();

            broadcast(new \App\Events\LoanStatusUpdated($loan));

            return response()->json(['loan' => $loan]);
        } catch (\Exception $e) {
            \Log::error("Update Loan Status Failed: ".$e->getMessage());
            return response()->json(['message' => 'Failed to update status'], 500);
        }
    }


}
