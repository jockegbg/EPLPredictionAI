<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_id',
        'predicted_home',
        'predicted_away',
        'points_awarded',
        'is_double_points',
        'points_adjustment', // Sidebets
        'is_defence_chip',
        'cashed_out_at',
        'cashout_points',
    ];

    protected $casts = [
        'is_double_points' => 'boolean',
        'is_defence_chip' => 'boolean',
        'cashed_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class);
    }
}
