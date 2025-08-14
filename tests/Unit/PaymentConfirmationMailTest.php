<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\PaymentConfirmationMail;
use App\Models\Loan;
use App\Models\Installment;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentConfirmationMailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_properties_subject_and_view()
    {
        $loan = Loan::factory()->create();
        $installment = Installment::factory()->for($loan)->create([
            'amount' => 1000,
            'status' => 'paid',
        ]);
        $payment = PaymentTransaction::factory()->for($installment)->create([
            'amount' => 1000,
            'status' => 'success',
            'processed_at' => now(),
        ]);

        $mail = new PaymentConfirmationMail($payment, $loan);

        $this->assertSame($payment->id, $mail->payment->id);
        $this->assertSame($loan->id, $mail->loan->id);

        $builtMail = $mail->build();
        $this->assertEquals('Payment Confirmation', $builtMail->subject);
        $this->assertEquals('emails.payment_confirmation', $builtMail->view);
    }
}
