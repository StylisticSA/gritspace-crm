<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\BankingDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    protected $guarded = [];

    public function banking()
    {
        return $this->hasMany(BankingDetail::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
