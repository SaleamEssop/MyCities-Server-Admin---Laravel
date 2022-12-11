<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regions extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'cost'];

    public function sites()
    {
        return $this->hasMany(Site::class, 'region_id');
    }

}
