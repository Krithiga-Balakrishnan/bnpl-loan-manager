<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\Installment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition()
    {
        return [
            'installment_id' => Installment::factory(),
            'amount' => $this->faker->numberBetween(500, 2000),
            'processed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['success', 'failed', 'pending']),
        ];
    }
}