<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankingDetail extends Model
{
    /** @use HasFactory<\Database\Factories\BankingDetailFactory> */
    use HasFactory;

    protected $guarded = [];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'banking_detail_id');
    }
}
