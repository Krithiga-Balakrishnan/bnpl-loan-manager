<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Customer; 
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['active', 'completed', 'pending']),
            'amount' => $this->faker->numberBetween(1000, 10000),
            'customer_id' => Customer::factory(),
            // add other fields required by your Loan model
        ];
    }
}
