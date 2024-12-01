<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmartCalendar extends Model
{
    protected $fillable = [
        'business_id',
        'event_type',
        'title',
        'suggestion',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}