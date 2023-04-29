<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionsAccountTypeCost extends Model
{
    use HasFactory;

    protected $table  = 'regions_account_type_cost';

    protected $fillable = [
        'template_name',
        'region_id',
        'account_type_id',
        'start_date',
        'end_date',
        'is_water',
        'is_electricity',
        'water_used',
        'electricity_used',
        'water_in',
        'water_out',
        'electricity',
        'additional',
        'vat_rate',
        'vat_percentage',
        'ratable_value',
        'rates_rebate',
        'is_active',
        'billing_day',
        'read_day',
        'waterin_additional',
        'waterout_additional',
        'electricity_additional',
        'water_email',
        'electricity_email'
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
