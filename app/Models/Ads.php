<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    use HasFactory;

    protected $fillable = ['ads_category_id', 'name', 'image', 'url', 'price', 'priority'];

    public function category()
    {
        return $this->belongsTo(AdsCategory::class, 'ads_category_id');
    }
}
