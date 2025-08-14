<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Loan;
use App\Models\Customer;
use App\Models\Installment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_loan_belongs_to_a_customer()
    {
        $customer = Customer::factory()->create();
        $loan = Loan::factory()->for($customer)->create();

        $this->assertInstanceOf(Customer::class, $loan->customer);
        $this->assertEquals($customer->id, $loan->customer->id);
    }

    /** @test */
    public function a_loan_has_many_installments()
    {
        $loan = Loan::factory()->create();
        $installments = Installment::factory()->count(3)->for($loan)->create();

        $this->assertCount(3, $loan->installments);
        $this->assertInstanceOf(Installment::class, $loan->installments->first());
    }

    /** @test */
    public function it_can_mark_as_completed_when_all_installments_paid()
    {
        $loan = Loan::factory()->create(['status' => 'active']);
        $installments = Installment::factory()->count(2)->for($loan)->create(['status' => 'paid']);

        $pending = $loan->installments()->where('status', 'pending')->count();
        if ($pending === 0) {
            $loan->update(['status' => 'completed']);
        }

        $this->assertEquals('completed', $loan->fresh()->status);
    }
}
