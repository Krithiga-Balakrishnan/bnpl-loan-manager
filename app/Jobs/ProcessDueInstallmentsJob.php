<?php

namespace App\Jobs;

use App\Models\Installment;
use App\Models\PaymentTransaction;
use App\Events\InstallmentPaid;
use App\Events\LoanCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentService;

class ProcessDueInstallmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PaymentService $paymentService)
    {
        $dueInstallments = Installment::with('loan')
            ->where('status', 'pending')
            ->where('due_date', '<=', now())
            ->get();

        foreach ($dueInstallments as $installment) {
            try {
                DB::transaction(function () use ($installment) {
                    $lockedInstallment = Installment::lockForUpdate()->find($installment->id);

                    if (!$lockedInstallment || $lockedInstallment->status !== 'pending') {
                        return;
                    }

                    $lockedInstallment->update(['status' => 'processing']);

                    // Simulate payment success
                    $success = true;

                    if ($success) {
                        $lockedInstallment->update([
                            'status'   => 'paid',
                            'paid_at'  => now(),
                        ]);

                        PaymentTransaction::create([
                            'installment_id' => $lockedInstallment->id,
                            'amount'         => $lockedInstallment->amount,
                            'processed_at'   => now(),
                            'status'         => 'success',
                        ]);

                        event(new InstallmentPaid($lockedInstallment->load('loan')));

                        $loan = $lockedInstallment->loan->fresh();
                        $pending = $loan->installments()->whereIn('status', ['pending', 'processing'])->count();

                        if ($pending === 0) {
                            $loan->update(['status' => 'completed']);
                            event(new LoanCompleted($loan));
                        }
                    } else {
                        $lockedInstallment->update(['status' => 'failed']);

                        PaymentTransaction::create([
                            'installment_id' => $lockedInstallment->id,
                            'amount'         => $lockedInstallment->amount,
                            'processed_at'   => now(),
                            'status'         => 'failed',
                        ]);

                        Log::warning("Payment failed for installment {$lockedInstallment->id}");
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Payment failed', [
                    'installment_id' => $installment->id,
                    'error'          => $e->getMessage()
                ]);
            }
        }
    }
}
