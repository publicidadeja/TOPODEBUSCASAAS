<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function getCurrentBusiness()
    {
        $currentBusinessId = session('current_business_id');
        
        if ($currentBusinessId) {
            return $this->businesses()->find($currentBusinessId);
        }
        
        return $this->businesses()->first();
    }
}