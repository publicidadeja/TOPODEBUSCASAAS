<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIFeedback extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'suggestion_id',
        'suggestion_type',
        'feedback_type',
        'comments',
        'applied',
        'effectiveness_score'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}