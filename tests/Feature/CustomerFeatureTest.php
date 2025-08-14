<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;

class CustomerFeatureTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_register_a_customer()
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'customer' => [
                         'id',
                         'name',
                         'email',
                         'phone',
                         'address',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
            'name'  => 'John Doe'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_unique_email_on_registration()
    {
        Customer::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/customers', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_all_customers()
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'customers');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_a_customer()
    {
        $customer = Customer::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com'
        ]);

        $response = $this->patchJson("/api/customers/{$customer->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '9876543210',
            'address' => '456 Another St'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'customer' => [
                         'id' => $customer->id,
                         'name' => 'New Name',
                         'email' => 'new@example.com',
                         'phone' => '9876543210',
                         'address' => '456 Another St'
                     ]
                 ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_unique_email_on_update()
    {
        $customer1 = Customer::factory()->create(['email' => 'first@example.com']);
        $customer2 = Customer::factory()->create(['email' => 'second@example.com']);

        $response = $this->patchJson("/api/customers/{$customer2->id}", [
            'name' => 'Another Name',
            'email' => 'first@example.com'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_required_fields_on_registration()
    {
        $response = $this->postJson('/api/customers', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_nullable_fields_on_registration()
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'phone' => null,
            'address' => null
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'Test User',
                    'email' => 'testuser@example.com',
                    'phone' => null,
                    'address' => null
                ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_email_format_on_registration()
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'Invalid Email',
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_required_fields_on_update()
    {
        $customer = Customer::factory()->create();

        $response = $this->patchJson("/api/customers/{$customer->id}", [
            'name' => '',
            'email' => ''
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
    }

}
