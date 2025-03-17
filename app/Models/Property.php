<?php

namespace App\Models;

use App\Models\User;
use App\Models\Account;
use App\Models\Payment;
use App\Models\FixedCostProp;
use App\Models\PropertyFixedCost;
use App\Models\RegionsAccountTypeCost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;
    protected $fillable = [ 'site_id','region_id','name', 'contact_person', 'address', 'description', 'phone', 'whatsapp', 'billing_day', 'property_manager_id',  'water_email',
    'electricity_email',];


    public function property_manager()
    {
        return $this->belongsTo(User::class, 'property_manager_id');
    }

    public function accounts()
{
    return $this->hasMany(Account::class, 'property_id', 'id');
}


  
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    //getting the cost of the region
    public function cost()
    {
        return $this->belongsTo(RegionsAccountTypeCost::class , 'region_cost_id');
    }

    public function fixedCostsProp()
    {
        return $this->hasMany(FixedCostProp::class);
    }

    public function defaultPropertyFixedCosts()
    {
        return $this->hasMany(PropertyFixedCost::class);
    }

    

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    //region
    public function region()
    {
        return $this->belongsTo(Regions::class);
    }




}
