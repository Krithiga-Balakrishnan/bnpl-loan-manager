<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\PaymentConfirmationMail;
use App\Models\Loan;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Mail;

class PaymentConfirmationMailFeatureTest extends TestCase
{
    /** @test */
    public function it_has_correct_properties_subject_and_view()
    {
        $loan = Loan::factory()->create();
        $payment = PaymentTransaction::factory()->for($loan->installments()->create([
            'amount' => 1000,
            'status' => 'paid',
            'due_date' => now(),
        ]))->create([
            'amount' => 1000,
            'status' => 'success',
            'processed_at' => now(),
        ]);

        $mail = new PaymentConfirmationMail($payment, $loan);

        // Check properties
        $this->assertSame($payment, $mail->payment);
        $this->assertSame($loan, $mail->loan);

        // Check subject
        $this->assertEquals('Payment Confirmation', $mail->build()->subject);

        // Check view
        $this->assertEquals('emails.payment_confirmation', $mail->build()->view);
    }
}
