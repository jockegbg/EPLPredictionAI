<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'gameweek_id',
        'home_team',
        'away_team',
        'home_score',
        'away_score',
        'start_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public function gameweek(): BelongsTo
    {
        return $this->belongsTo(Gameweek::class);
    }

    public function getHomeTeamLogoAttribute()
    {
        $slug = \Illuminate\Support\Str::slug($this->home_team);
        return asset("images/teams/{$slug}.png");
    }

    public function getAwayTeamLogoAttribute()
    {
        $slug = \Illuminate\Support\Str::slug($this->away_team);
        return asset("images/teams/{$slug}.png");
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'match_id');
    }
}
