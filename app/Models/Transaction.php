<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['transaction_number', 'subtotal', 'discount', 'total_amount', 'paid_amount', 'change_amount', 'payment_method'];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
