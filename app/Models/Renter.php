<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rent_amount',
        'payment_method',
        'banker_name',
        'deposit_amount',
        'deposit_payment_method',
        'deposit_banker_name',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
