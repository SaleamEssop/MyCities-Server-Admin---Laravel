<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'meter_type_id',
        'meter_title',
        'meter_number'
    ];

    public function readings()
    {
        return $this->hasMany(MeterReadings::class);
    }
}
