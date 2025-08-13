<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;
    
    protected $fillable = ['installment_id', 'amount', 'processed_at', 'status'];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }
}
