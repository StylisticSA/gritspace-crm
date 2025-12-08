<?php

namespace App\Models;

use App\Models\User;
use App\Models\Extra;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Printing extends Model
{
    /** @use HasFactory<\Database\Factories\PrintingFactory> */
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

    public static function calculateTotalCost(int $locationId, float $blackAmount, float $colorAmount): array
    {
        $names = [];
        if ($blackAmount > 0) {
            $names[] = 'Black';
        }
        if ($colorAmount > 0) {
            $names[] = 'Color';
        }

        $prices = Extra::select('name', 'price')
            ->where('location_id', $locationId)
            ->whereIn('name', $names)
            ->get()
            ->keyBy('name');

        return [
            'black_total_cost' => ($prices['Black']->price ?? 0) * $blackAmount,
            'color_total_cost' => ($prices['Color']->price ?? 0) * $colorAmount,
        ];
    }


}
