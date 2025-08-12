<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = ['amount', 'status'];

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }
}
