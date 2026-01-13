<?php

namespace App\Services;

use App\Models\GameMatch;

class ScoringService
{
    public function calculatePoints(GameMatch $match): void
    {
        // Only calculate if match is completed and scores are set
        if ($match->status !== 'completed' || is_null($match->home_score) || is_null($match->away_score)) {
            return;
        }

        $homeScore = $match->home_score;
        $awayScore = $match->away_score;

        // Determine match outcome
        $matchResult = 'draw';
        if ($homeScore > $awayScore) {
            $matchResult = 'home_win';
        } elseif ($awayScore > $homeScore) {
            $matchResult = 'away_win';
        }

        // Calculate points for all predictions
        foreach ($match->predictions as $prediction) {
            $points = 0;

            // Determine prediction outcome
            $predResult = 'draw';
            if ($prediction->predicted_home > $prediction->predicted_away) {
                $predResult = 'home_win';
            } elseif ($prediction->predicted_away > $prediction->predicted_home) {
                $predResult = 'away_win';
            }

            // Scoring Logic
            if ($prediction->predicted_home == $homeScore && $prediction->predicted_away == $awayScore) {
                // Exact score
                $points = 40;
            } elseif ($predResult === $matchResult) {
                // Correct outcome (but not exact score)
                $points = 10;
            }

            // Apply Double Chip multiplier
            if ($prediction->is_double_points) {
                $points *= 2;
            }

            $prediction->update(['points_awarded' => $points]);
        }
    }
}
