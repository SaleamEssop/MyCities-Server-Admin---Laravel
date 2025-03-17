<?php

namespace App\Models;

use App\Models\User;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'site_id',
        'account_name',
        'account_number',
        'billing_date',
        'optional_information',
        'region_id',
        'account_type_id',
        'water_email',
        'electricity_email',
        'bill_day',
        'read_day',
        'bill_read_day_active'


    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    //region
    public function region()
    {
        return $this->belongsTo(Regions::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function fixedCosts()
    {
        return $this->hasMany(FixedCost::class);
    }

    public function defaultFixedCosts()
    {
        return $this->hasMany(AccountFixedCost::class);
    }

    public function meters()
    {
        return $this->hasMany(Meter::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected static function booted()
    {
        static::deleted(function ($account) {
            /*foreach($account->fixedCosts as $fixedCost) {
                FixedCost::where('id', $fixedCost->id)->first()->delete();
            }*/

            FixedCost::where('account_id', $account->id)->delete();
            AccountFixedCost::where('account_id', $account->id)->delete();

            foreach ($account->meters as $meter) {
                Meter::where('id', $meter->id)->first()->delete();
            }
        });
    }
}
