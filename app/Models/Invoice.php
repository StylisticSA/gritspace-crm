<?php

namespace App\Models;

use App\Models\InvoiceItem;
use App\Models\BankingDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function banking()
    {
        return $this->belongsTo(BankingDetail::class, 'banking_detail_id');
    }
}
