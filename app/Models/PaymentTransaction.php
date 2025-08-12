<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['installment_id', 'amount', 'processed_at', 'status'];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }
}
