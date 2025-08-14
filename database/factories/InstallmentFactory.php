<?php

namespace Database\Factories;

use App\Models\Installment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    public function definition()
    {
        return [
            'loan_id' => Loan::factory(),
            'amount' => $this->faker->numberBetween(500, 2000),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'paid_at' => null, // or use $this->faker->optional()->dateTimeBetween('-1 month', 'now')
            'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
        ];
    }
}