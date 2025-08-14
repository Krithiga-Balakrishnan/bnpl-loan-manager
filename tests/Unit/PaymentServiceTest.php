<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Loan;
use App\Models\Installment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_process_installment_successfully()
    {
        $loan = Loan::factory()->create();
        $installment = Installment::factory()->for($loan)->create(['status' => 'pending']);

        $service = new PaymentService();

        $result = $service->processInstallment($installment);

        $this->assertTrue($result);
        $this->assertDatabaseHas('installments', ['id' => $installment->id, 'status' => 'paid']);
        $this->assertDatabaseCount('payment_transactions', 1);
    }

    /** @test */
    public function it_returns_false_for_already_paid_installment()
    {
        $loan = Loan::factory()->create();
        $installment = Installment::factory()->for($loan)->create(['status' => 'paid']);

        $service = new PaymentService();

        $result = $service->processInstallment($installment);

        $this->assertFalse($result);
    }
}
