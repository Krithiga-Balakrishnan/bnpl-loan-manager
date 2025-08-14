<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\PaymentTransaction;

class PaymentService
{
    public function processInstallment(Installment $installment): bool
    {
        if ($installment->status !== 'pending') {
            return false;
        }

        // if ($installment->status === 'paid') {
        //     return true;
        // }
        
        // Simulate payment processing
        $success = true;

        if ($success) {
            $installment->update(['status' => 'paid', 'paid_at' => now()]);

            PaymentTransaction::create([
                'installment_id' => $installment->id,
                'amount' => $installment->amount,
                'processed_at' => now(),
                'status' => 'success'
            ]);
        }

        return $success;
    }
}
