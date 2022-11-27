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

    /*public function defaultFixedCosts()
    {
        return $this->hasManyThrough(AccountFixedCost::class, FixedCost::class, 'account_id', 'fixed_cost_id');
    }*/
}
