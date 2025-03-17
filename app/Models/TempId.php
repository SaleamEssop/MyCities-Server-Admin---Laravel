<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempId extends Model
{
    protected $fillable = ['token', 'user_id', 'account_id'];
}
