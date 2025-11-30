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
        'billing_type',
        'site_username',
        'site_password'
    ];

    protected $hidden = [
        'site_password'
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
