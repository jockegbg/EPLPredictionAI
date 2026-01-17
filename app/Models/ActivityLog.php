<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action', // 'login', 'page_visit', 'prediction', 'logout'
        'method', // 'password', 'passkey', 'GET', 'POST'
        'ip_address',
        'user_agent',
        'details', // JSON
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
