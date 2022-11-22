<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReadings extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id',
        'reading_date',
        'reading_value'
    ];

    public function meter() {
        return $this->belongsTo(Meter::class);
    }
}
