<?php

namespace App\Models;

use App\Models\Property;
use App\Models\PropertyFixedCost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedCostProp extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'title',
        'value',
        'is_default',
        'added_by',
        'is_active',
        'created_at'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function defaultCosts()
    {
        return $this->hasMany(PropertyFixedCost::class);
    }

    protected static function booted()
    {
        static::deleted(function ($fixedCost) {
            foreach($fixedCost->defaultCosts as $accFixedCost) {
                PropertyFixedCost::where('id', $accFixedCost->id)->first()->delete();
            }
        });
    }
}
