<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Prediction;

class ScoringService
{
    public function calculatePoints(GameMatch $match): void
    {
        // Only calculate if match is completed and scores are set
        if ($match->status !== 'completed' || is_null($match->home_score) || is_null($match->away_score)) {
            return;
        }

        foreach ($match->predictions as $prediction) {
            // SKIP IF CASHED OUT
            if ($prediction->cashed_out_at) {
                continue;
            }

            $points = $this->calculatePredictionScore($match, $prediction);
            $prediction->update(['points_awarded' => $points]);
        }
    }

    public function calculatePredictionScore(GameMatch $match, Prediction $prediction): int
    {
        $homeScore = $match->home_score;
        $awayScore = $match->away_score;
        $matchDiff = $homeScore - $awayScore;

        // Determine match outcome
        $matchResult = 'draw';
        if ($homeScore > $awayScore) {
            $matchResult = 'home_win';
        } elseif ($awayScore > $homeScore) {
            $matchResult = 'away_win';
        }

        // Get Tournament Settings
        $tournament = $match->gameweek->tournament;
        $settings = [
            'exact' => $tournament->score_correct_score ?? 40,
            'outcome' => $tournament->score_correct_outcome ?? 10,
            'diff' => $tournament->score_goal_difference ?? 0,
            'penalty' => $tournament->score_wrong_outcome_penalty ?? 0,
        ];

        $points = 0;
        $predHome = $prediction->predicted_home;
        $predAway = $prediction->predicted_away;
        $predDiff = $predHome - $predAway;

        // Determine prediction outcome
        $predResult = 'draw';
        if ($predHome > $predAway) {
            $predResult = 'home_win';
        } elseif ($predAway > $predHome) {
            $predResult = 'away_win';
        }

        // SCORING LOGIC HIERARCHY
        if ($predHome == $homeScore && $predAway == $awayScore) {
            // 1. Exact score
            $points = $settings['exact'];
        } elseif ($predResult === $matchResult && $predDiff === $matchDiff && $settings['diff'] > 0 && $matchResult !== 'draw') {
            // 2. Correct Goal Difference (and Outcome) - EXCLUDING DRAWS
            $points = $settings['diff'];
        } elseif ($predResult === $matchResult) {
            // 3. Correct Outcome
            $points = $settings['outcome'];
        } else {
            // 4. WRONG Outcome -> Apply Penalty?
            if ($settings['penalty'] > 0) {
                // Check Defence Chip
                if (!$prediction->is_defence_chip) {
                    $points = -1 * abs($settings['penalty']);
                }
            }
        }

        // Apply Double Chip multiplier
        if ($prediction->is_double_points) {
            $points *= 2;
        }

        // Apply Sidebet Adjustment
        $points += $prediction->points_adjustment;

        return $points;
    }
}
