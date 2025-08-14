<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_customer_can_have_multiple_loans()
    {
        $customer = Customer::factory()->create();
        $loans = Loan::factory()->count(3)->for($customer)->create();

        $this->assertCount(3, $customer->loans);
        $this->assertInstanceOf(Loan::class, $customer->loans->first());
    }

    /** @test */
    public function it_can_update_customer_details()
    {
        $customer = Customer::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $customer->update([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertEquals('Updated Name', $customer->fresh()->name);
        $this->assertEquals('updated@example.com', $customer->fresh()->email);
    }
}
