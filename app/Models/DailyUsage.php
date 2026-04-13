<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyUsage extends Model
{
    /** @use HasFactory<\Database\Factories\DailyUsageFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function calculateTotalCost(int $locationId, float $Amount, string $name): array
    {

        $prices = Extra::select('name', 'price')
                ->where('location_id', $locationId)
                ->where('name', $name)
                ->get()
                ->keyBy('name');
                
        dd($prices);

        return [
            'total_cost' => ($prices['Coffee']->price ?? 0) * $Amount,

        ];
    }
}
