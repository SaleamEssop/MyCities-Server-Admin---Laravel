<?php

namespace App\Models;

use App\Traits\CommonModelFunctions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class MeterReadings extends Model
{
    use HasFactory,CommonModelFunctions;

    protected $fillable = [
        'meter_id',
        'reading_date',
        'reading_value',
        'reading_image'
    ];

    public function meter() {
        return $this->belongsTo(Meter::class);
    }

    public function getReadingImageAttribute($value)
    {
        if (empty($value))
            return '';
        return URL::to(Storage::url($value));
    }
}
