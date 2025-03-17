<?php

namespace App\Models;

use App\Models\Meter;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id', 'start_date', 'end_date', 'start_reading', 'end_reading',
        'start_reading_id', 'end_reading_id', 'usage_liters', 'cost',
        'consumption_charge', 'discharge_charge', 'additional_costs',
        'water_out_additional', 'vat', 'daily_usage', 'daily_cost', 'status'
    ];

    protected $casts = [
        'additional_costs' => 'array',
        'water_out_additional' => 'array'
    ];
    

    protected $dates = ['start_date', 'end_date'];

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'billing_period_id');
    }

  
    
}
