<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'amount',
        'payment_method',
        'banker_name',
        'date',
        'renter_id',
        'expense_party_id',
        'category',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function renter()
    {
        return $this->belongsTo(Renter::class);
    }

    public function expenseParty()
    {
        return $this->belongsTo(ExpenseParty::class);
    }
}
