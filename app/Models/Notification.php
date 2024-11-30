<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'type',
        'title',
        'message',
        'action_url',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isUnread()
    {
        return is_null($this->read_at);
    }
}