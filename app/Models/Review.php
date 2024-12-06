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
        'comment'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}