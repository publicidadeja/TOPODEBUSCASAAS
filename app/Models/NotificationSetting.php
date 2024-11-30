<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'metric_type',
        'condition',
        'threshold',
        'email_enabled',
        'app_enabled'
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'app_enabled' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}