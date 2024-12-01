<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'business_id',
        'title',
        'event_type',
        'start_date',
        'end_date',
        'suggestion',
        'status',
        'color',
        'description'
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