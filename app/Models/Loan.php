<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{

    use HasFactory;

    protected $fillable = ['amount', 'status', 'customer_id'];

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // public function getCustomerEmailAttribute()
    // {
    //     return $this->customer->email ?? null;
    // }

    // public function getCustomerNameAttribute()
    // {
    //     return $this->customer->name ?? 'Customer';
    // }

}
