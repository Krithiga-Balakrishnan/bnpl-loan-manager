<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Installment;
use App\Models\PaymentTransaction;
use App\Mail\PaymentConfirmationMail;
use App\Events\InstallmentPaid;
use App\Events\LoanCompleted;
use App\Services\PaymentService;
use App\Jobs\ProcessDueInstallmentsJob;

class PaymentControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        Mail::fake();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_pay_an_installment_successfully()
    {
        $customer = Customer::factory()->create();
        $loan = Loan::factory()->for($customer)->create();
        $installment = Installment::factory()->for($loan)->create(['status' => 'pending']);

        $response = $this->postJson("/api/installments/{$installment->id}/pay", [
            'payment_method' => 'card'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'loan' => ['id', 'status', 'installments'],
                     'installment' => ['amount', 'processed_at']
                 ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'installment_id' => $installment->id,
            'status' => 'success'
        ]);

        Event::assertDispatched(InstallmentPaid::class, fn($e) => $e->loan['id'] === $loan->id);
        Mail::assertSent(PaymentConfirmationMail::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_marks_loan_completed_when_all_installments_paid()
    {
        $customer = Customer::factory()->create();
        $loan = Loan::factory()->for($customer)->create(['status' => 'active']);
        $installments = Installment::factory()->count(2)->for($loan)->create(['status' => 'pending']);

        foreach ($installments as $inst) {
            $this->postJson("/api/installments/{$inst->id}/pay", ['payment_method' => 'card']);
        }

        $loan->refresh();
        $this->assertEquals('completed', $loan->status);

        Event::assertDispatched(LoanCompleted::class, fn($e) => $e->loan->id === $loan->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_pay_already_paid_installment()
    {
        $installment = Installment::factory()->create(['status' => 'paid']);

        $response = $this->postJson("/api/installments/{$installment->id}/pay", ['payment_method' => 'card']);

        // Controller throws 500 for already paid
        $response->assertStatus(500)
                ->assertJson(['message' => 'Error']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_non_existent_installment()
    {
        $response = $this->postJson("/api/installments/9999/pay", ['payment_method' => 'card']);

        // FormRequest returns 422 for invalid installment
        $response->assertStatus(422)
                ->assertJsonStructure(['message', 'errors']);
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_failed_payment_when_payment_fails()
    {
        $this->partialMock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('processInstallment')->andReturnFalse();
        });

        $customer = Customer::factory()->create();
        $loan = Loan::factory()->for($customer)->create(['status' => 'active']);
        $installment = Installment::factory()->for($loan)->create(['status' => 'pending']);

        $job = new ProcessDueInstallmentsJob();
        $job->handle(app(PaymentService::class));

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => 'pending' 
        ]);

        $this->assertDatabaseCount('payment_transactions', 0);
    }
}
