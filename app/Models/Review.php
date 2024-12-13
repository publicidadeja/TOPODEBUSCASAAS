<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'author',
        'rating',
        'comment',
        'scheduled_date',
        'notes',
        'status'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime'
    ];


    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}