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
        'ai_commentary',
        'minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'ai_commentary' => 'array',
    ];

    public function gameweek(): BelongsTo
    {
        return $this->belongsTo(Gameweek::class);
    }

    public function getHomeTeamLogoAttribute()
    {
        $slug = \Illuminate\Support\Str::slug($this->home_team);
        $path = "images/teams/{$slug}.png";

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset("images/teams/default_badge.svg");
    }

    public function getAwayTeamLogoAttribute()
    {
        $slug = \Illuminate\Support\Str::slug($this->away_team);
        $path = "images/teams/{$slug}.png";

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset("images/teams/default_badge.svg");
    }

    public function getDisplayMinutesAttribute()
    {
        if ($this->status !== 'in_progress') {
            return null;
        }

        $lastMinutes = $this->minutes ?? 0;

        // Calculate minutes elapsed since last DB update
        // We use 'updated_at' 
        $minutesSinceUpdate = (int) $this->updated_at->diffInMinutes(now());
        $currentMinute = (int) $lastMinutes + $minutesSinceUpdate;

        // Logic to cap at half-time / full-time markers if we suspect it
        // This is heuristic because we don't have strict 'HT' state in DB yet (just 'in_progress')
        // Standard halves are 45 min.

        // If we are around 45+, limit to 45+ until update pushes it over
        // Assumption: API pushes ~46 when 2nd half starts.
        if ($lastMinutes <= 45 && $currentMinute > 45) {
            return "45+'";
        }

        // If we are around 90+, limit to 90+
        if ($lastMinutes <= 90 && $currentMinute > 90) {
            return "90+'";
        }

        // Safety cap for extremely long intervals (e.g. sync broken for hours)
        if ($currentMinute > 130) {
            return "FT?"; // Or just 90+
        }

        return $currentMinute . "'";
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'match_id');
    }
}
