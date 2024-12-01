<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'description',
        'result',
        'status'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}