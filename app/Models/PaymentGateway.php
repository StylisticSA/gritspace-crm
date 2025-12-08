<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentGatewayFactory> */
    use HasFactory;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'transaction_id',
        'amount',
        'currency',
        'plan',
        'status',
        'assurance_data',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}
