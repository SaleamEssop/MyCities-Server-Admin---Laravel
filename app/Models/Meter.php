<?php

namespace App\Models;

use App\Models\Payment;
use App\Models\BillingPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meter extends Model
{
    use HasFactory;

    protected $table = 'meters';

    protected $fillable = [
        'account_id',
        'meter_category_id',
        'meter_type_id',
        'meter_title',
        'meter_number'
    ];

    public function readings()
    {
        return $this->hasMany(MeterReadings::class);
    }

    public function meterTypes()
    {
        return $this->belongsTo(MeterType::class, 'meter_type_id');
    }

    public function meterCategory()
    {
        return $this->belongsTo(MeterCategory::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function billings()
    {
        return $this->hasMany(BillingPeriod::class, 'meter_id');
    }


    protected static function booted()
    {
        static::deleted(function ($meter) {
            MeterReadings::where('meter_id', $meter->id)->delete();
        });
    }
}
