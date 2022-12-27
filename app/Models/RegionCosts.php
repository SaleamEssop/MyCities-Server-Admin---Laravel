<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionCosts extends Model
{
    use HasFactory;

    protected $fillable = ['meter_type_id', 'region_id', 'min', 'max', 'amount'];
}
