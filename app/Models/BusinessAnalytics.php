<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAnalytics extends Model
{
    use HasFactory;

    protected $table = 'analytics';

    protected $fillable = [
        'business_id',
        'views',
        'clicks',
        'calls',
        'search_keywords',
        'user_locations',
        'devices',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
        'devices' => 'array',
        'user_locations' => 'array',
        'search_keywords' => 'array'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Mutators para lidar com JSON como texto
    public function setSearchKeywordsAttribute($value)
    {
        $this->attributes['search_keywords'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getSearchKeywordsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setUserLocationsAttribute($value)
    {
        $this->attributes['user_locations'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getUserLocationsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setDevicesAttribute($value)
    {
        $this->attributes['devices'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getDevicesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}