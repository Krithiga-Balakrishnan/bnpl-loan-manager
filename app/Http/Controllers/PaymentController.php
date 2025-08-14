<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Installment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\PayInstallmentRequest;


class PaymentController extends Controller
{
    public function pay(PayInstallmentRequest $request, $id)
    {
try {
        $loan = null;
        $inst = null;

        DB::transaction(function () use ($id, &$loan, &$inst) {
            $inst = Installment::lockForUpdate()->findOrFail($id);

            if ($inst->status !== 'pending') {
                abort(400, 'Installment already processed');
            }

            // Simulated payment result
            $success = true;

            if ($success) {
                $inst->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);

                $payment = PaymentTransaction::create([
                    'installment_id' => $inst->id,
                    'amount'         => $inst->amount,
                    'processed_at'   => now(),
                    'status'         => 'success',
                ]);

    $loan = $inst->loan->fresh(['installments']);

    if ($loan->installments->where('status', 'pending')->isEmpty()) {
        $loan->update(['status' => 'completed']);
        event(new \App\Events\LoanCompleted($loan));
    }
                event(new \App\Events\InstallmentPaid($inst->load('loan.installments')));
                // Send email
                Mail::to($loan->customer->email)
                    ->send(new PaymentConfirmationMail($payment, $loan));
            } else {
                $inst->update(['status' => 'failed']);

                PaymentTransaction::create([
                    'installment_id' => $inst->id,
                    'amount'         => $inst->amount,
                    'processed_at'   => now(),
                    'status'         => 'failed',
                ]);

                abort(500, 'Payment failed');
            }
        });

        return response()->json([
            'message' => 'Installment paid',
            'loan' => $loan, 
            'installment' => [
                'amount'       => $inst->amount,
                'processed_at' => now()->toDateTimeString(),
            ]
        ], 200);

    } catch (\Exception $e) {
        \Log::error("Pay Installment Failed: " . $e->getMessage());
        return response()->json([
            'message' => 'Error',
            'error'   => $e->getMessage()
        ], 500);
    }
    }
}
