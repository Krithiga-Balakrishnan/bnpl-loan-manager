<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use App\Models\Loan;
use App\Models\Installment;
use App\Models\Customer;
use App\Models\PaymentTransaction;

class LoanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake broadcasting so Pusher is not called
        Event::fake();

        // Seed customers for loans
        Customer::factory()->count(2)->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_generate_a_loan_with_installments()
    {
        $customer = Customer::first();

        $response = $this->postJson('/api/loans/generate', [
            'customer_id' => $customer->id,
            'loan_amount' => 1200,
            'number_of_loans' => 1,
            'installments_per_loan' => 4,
            'installment_period_minutes' => 60
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'loans' => [
                         '*' => [
                             'id',
                             'customer_id',
                             'amount',
                             'status',
                             'installments'
                         ]
                     ]
                 ]);

        $this->assertDatabaseHas('loans', [
            'customer_id' => $customer->id,
            'amount' => 1200,
        ]);

        $loan = Loan::first();
        $this->assertCount(4, $loan->installments);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_loan_status()
    {
        $loan = Loan::factory()->for(Customer::first())->create([
            'status' => 'active'
        ]);

        $response = $this->patchJson("/api/loans/{$loan->id}/status", [
            'status' => 'completed'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['loan' => ['status' => 'completed']]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'completed'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_payments()
    {
        $loan = Loan::factory()->for(Customer::first())->create();
        $installment = Installment::factory()->for($loan)->create();
        PaymentTransaction::factory()->for($installment)->create([
            'status' => 'success'
        ]);

        $response = $this->getJson('/api/loans/payments');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_correct_loan_status_counts()
    {
        Loan::factory()->for(Customer::first())->create(['status' => 'active']);
        Loan::factory()->for(Customer::first())->create(['status' => 'completed']);
        Loan::factory()->for(Customer::first())->create(['status' => 'cancelled']);

        $response = $this->getJson('/api/loans/status-counts');

        $response->assertStatus(200)
                 ->assertJson([
                     'active' => 1,
                     'completed' => 1,
                     'cancelled' => 1
                 ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_updating_status_for_invalid_loan()
    {
        $response = $this->patchJson("/api/loans/999/status", [
            'status' => 'completed'
        ]);

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_when_no_successful_payments()
    {
        $response = $this->getJson('/api/loans/payments');
        $response->assertStatus(200)
                ->assertExactJson([]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_excludes_failed_payments()
    {
        $loan = Loan::factory()->for(Customer::first())->create();
        $installment = Installment::factory()->for($loan)->create();
        PaymentTransaction::factory()->for($installment)->create([
            'status' => 'failed'
        ]);

        $response = $this->getJson('/api/loans/payments');
        $response->assertStatus(200)
                ->assertExactJson([]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_zero_counts_when_no_loans()
    {
        $response = $this->getJson('/api/loans/status-counts');

        $response->assertStatus(200)
                ->assertJson([
                    'active' => 0,
                    'completed' => 0,
                    'cancelled' => 0
                ]);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_generate_multiple_loans_at_once()
    {
        $customer = Customer::first();

        $response = $this->postJson('/api/loans/generate', [
            'customer_id' => $customer->id,
            'loan_amount' => 1000,
            'number_of_loans' => 3,
            'installments_per_loan' => 2
        ]);

        $response->assertStatus(201)
                ->assertJsonCount(3, 'loans');

        $this->assertDatabaseCount('loans', 3);

        foreach (Loan::all() as $loan) {
            $this->assertCount(2, $loan->installments);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_dispatches_loan_generated_event()
    {
        $customer = Customer::first();

        Event::fake();

        $this->postJson('/api/loans/generate', [
            'customer_id' => $customer->id,
            'loan_amount' => 500,
            'number_of_loans' => 1,
            'installments_per_loan' => 2
        ])->assertStatus(201);

        Event::assertDispatched(\App\Events\LoanGenerated::class, function ($event) use ($customer) {
            return $event->loan->customer_id === $customer->id;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_dispatches_loan_status_updated_event()
    {
        $loan = Loan::factory()->for(Customer::first())->create(['status' => 'active']);

        Event::fake();

        $this->patchJson("/api/loans/{$loan->id}/status", [
            'status' => 'completed'
        ])->assertStatus(200);

        Event::assertDispatched(\App\Events\LoanStatusUpdated::class, function ($event) use ($loan) {
            return $event->loan->id === $loan->id && $event->loan->status === 'completed';
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_generate_loan_for_invalid_customer()
    {
        $response = $this->postJson('/api/loans/generate', [
            'customer_id' => 9999,
            'loan_amount' => 1000,
            'number_of_loans' => 1
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors('customer_id');
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_loan_generation_request()
    {
        $response = $this->postJson('/api/loans/generate', [
            'customer_id' => null,
            'loan_amount' => -100,
            'number_of_loans' => 0
        ]);

        $response->assertStatus(422);
    }


}


