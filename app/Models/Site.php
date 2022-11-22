<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'lat',
        'lng',
        'address',
        'email',
    ];

    public function account()
    {
        return $this->hasMany(Account::class);
    }
}
