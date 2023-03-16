<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionsAccountTypeCost extends Model
{
    use HasFactory;

    protected $table  = 'regions_account_type_cost';

    protected $fillable = [
        'region_id',
        'account_type_id',
        'meter_type_id',
        'water_in',
        'water_out',
        'garbase_collection_cost',
        'infrastructure_levy_cost',
        'vat_rate',
        'vat_percentage',
        'is_active',
    ];

    public function region()
    {
        return $this->belongsTo(Regions::class);
    }
    public function meterType()
    {
        return $this->belongsTo(MeterType::class);
    }
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
