<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $loan;

    public function __construct($payment, $loan)
    {
        $this->payment = $payment;
        $this->loan = $loan;
    }

    public function build()
    {
        return $this->subject('Payment Confirmation')
                    ->view('emails.payment_confirmation');
    }
}

