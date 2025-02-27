<?php

namespace App\Models;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\URL;
use App\Traits\CommonModelFunctions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeterReadings extends Model
{
    use HasFactory, CommonModelFunctions;

    protected $fillable = [
        'meter_id',
        'reading_date',
        'reading_value',
        'reading_image',
        'added_by',
    ];

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'reading_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function getReadingImageAttribute($value)
    {
        if (empty($value))
            return '';
        return URL::to(Storage::url($value));
    }
}
