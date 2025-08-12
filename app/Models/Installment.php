<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable = ['loan_id', 'amount', 'due_date', 'paid_at', 'status'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
