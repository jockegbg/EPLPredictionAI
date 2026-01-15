<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'score_correct_score',
        'score_correct_outcome',
        'score_goal_difference',
        'score_wrong_outcome_penalty',
        'is_cashout_enabled',
        'is_double_down_enabled',
        'is_defence_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_cashout_enabled' => 'boolean',
        'is_double_down_enabled' => 'boolean',
        'is_defence_enabled' => 'boolean',
    ];

    public function gameweeks()
    {
        return $this->hasMany(Gameweek::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
