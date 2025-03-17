<?php

namespace App\Models;

use App\Models\Property;
use App\Models\FixedCostProp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyFixedCost extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'fixed_cost_id',
        'value',
        'is_active'
    ];


    public function fixedCostProp()
    {
        return $this->belongsTo(FixedCostProp::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function getFixedCostsProp() {
        return $this->hasOne(FixedCostProp::class, 'id', 'fixed_cost_id');
    }
}
