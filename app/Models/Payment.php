<?php

namespace App\Models;

use App\Models\User;
use App\Models\Meter;
use App\Models\Account;
use App\Models\BillingPeriod;
use App\Models\MeterReadings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'account_id', 'meter_id', 'reading_id', 'payment_date', 'payment_method', 'amount', 'status', 'billing_period_id', 'paid_amount','total_paid_amount',];
    protected $dates = ['payment_date'];
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Payment belongs to a user (who made the payment).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payment is related to a meter (optional).
     */
    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    /**
     * Payment is linked to a specific meter reading (optional).
     */
    // public function reading()
    // {
    //     return $this->belongsTo(MeterReadings::class, 'reading_id');
    // }

    public function reading()
    {
        return $this->belongsTo(MeterReadings::class, 'reading_id');
    }


    public function billingPeriod()
    {
        return $this->belongsTo(BillingPeriod::class, 'billing_period_id');
    }
}
