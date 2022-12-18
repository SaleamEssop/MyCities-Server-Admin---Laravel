<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regions extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'electricity_base_unit_cost','electricity_base_unit', 'water_base_unit_cost', 'water_base_unit', 'cost'];

    public function sites()
    {
        return $this->hasMany(Site::class, 'region_id');
    }

    public function alarms()
    {
        return $this->hasMany(RegionAlarms::class, 'region_id');
    }

}
