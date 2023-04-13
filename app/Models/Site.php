<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'user_id',
        'title',
        'lat',
        'lng',
        'address',
        'email',
        'account_type_id',
        'water_email',
        'electricity_email'
    ];

    public function account()
    {
        return $this->hasMany(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Regions::class);
    }

    protected static function booted()
    {
        static::deleted(function ($site) {
            foreach($site->account as $account) {
                Account::where('id', $account->id)->first()->delete();
            }
        });
    }
}
