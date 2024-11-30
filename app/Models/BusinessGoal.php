<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'year',
        'month',
        'monthly_views_goal',
        'monthly_clicks_goal',
        'conversion_rate_goal',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}