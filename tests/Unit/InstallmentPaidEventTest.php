<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Events\InstallmentPaid;
use App\Models\Loan;
use App\Models\Installment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InstallmentPaidEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_contains_loan_data_with_installments()
    {
        $loan = Loan::factory()->create();
        $installment = Installment::factory()->for($loan)->create();

        $event = new InstallmentPaid($installment);

        $this->assertArrayHasKey('id', $event->loan);
        $this->assertArrayHasKey('installments', $event->loan);
    }
}
