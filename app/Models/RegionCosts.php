<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionCosts extends Model
{
    use HasFactory;

    protected $fillable = ['meter_type_id', 'start_date', 'end_date', 'region_id', 'min', 'max', 'amount'];
    protected $dates = [
        'start_date',
        'end_date'
    ];
}
