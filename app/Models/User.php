<?php

namespace App\Models;

use App\Models\Payment;
use App\Models\Property;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'contact_number',
        'password',
        'is_admin',
        'is_super_admin',
        'is_property_manager',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    protected static function booted()
    {
        static::deleted(function ($user) {
            foreach($user->sites as $site) {
                Site::where('id', $site->id)->first()->delete();
            }
        });
    }

    //one-to-many relationship with Property model
    public function properties()
    {
        return $this->hasMany(Property::class, 'property_manager_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function hasPermission($permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }
}
