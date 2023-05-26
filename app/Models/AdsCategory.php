<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name','parent_id'];

    public function ads()
    {
        return $this->hasMany(Ads::class)->orderByRaw('ISNULL(priority), priority ASC');
    }
}
