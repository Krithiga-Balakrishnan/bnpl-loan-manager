<?php

namespace App\Jobs;

use App\Models\Installment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDueInstallmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(PaymentService $paymentService)
    {
        $dueInstallments = Installment::where('status', 'pending')
            ->where('due_date', '<=', now())
            ->with('loan')
            ->get();

        foreach ($dueInstallments as $installment) {
            try {
                $result = $paymentService->processInstallment($installment);

                if (!$result) {
                    Log::info("Skipped installment {$installment->id} — already processed or failed");
                }
            } catch (\Throwable $e) {
                Log::error('Payment processing error', [
                    'installment_id' => $installment->id,
                    'error'          => $e->getMessage()
                ]);
            }
        }
    }
}
