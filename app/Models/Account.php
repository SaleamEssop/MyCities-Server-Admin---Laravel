<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'account_name',
        'account_number',
        'billing_date',
        'optional_information'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
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

    protected static function booted()
    {
        static::deleted(function ($account) {
            /*foreach($account->fixedCosts as $fixedCost) {
                FixedCost::where('id', $fixedCost->id)->first()->delete();
            }*/

            FixedCost::where('account_id', $account->id)->delete();
            AccountFixedCost::where('account_id', $account->id)->delete();

            foreach($account->meters as $meter) {
                Meter::where('id', $meter->id)->first()->delete();
            }
        });
    }

}
