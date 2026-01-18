<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request): View
    {
        $tournaments = \App\Models\Tournament::orderByDesc('created_at')->get();
        $currentTournament = $request->has('tournament_id')
            ? $tournaments->find($request->get('tournament_id'))
            : $tournaments->firstWhere('is_active', true) ?? $tournaments->first();

        // Calculate points ONLY for the selected tournament
        $query = User::withSum([
            'predictions' => function ($query) use ($currentTournament) {
                if ($currentTournament) {
                    $query->whereHas('match', function ($q) use ($currentTournament) {
                        $q->whereHas('gameweek', function ($gw) use ($currentTournament) {
                            $gw->where('tournament_id', $currentTournament->id);
                        });
                    });
                }
            }
        ], 'points_awarded');

        if ($currentTournament) {
            $query->whereHas('tournaments', function ($q) use ($currentTournament) {
                $q->where('tournaments.id', $currentTournament->id);
            });

            // Eager load predictions for stat calculation
            $query->with([
                'predictions' => function ($q) use ($currentTournament) {
                    $q->whereHas('match', function ($m) use ($currentTournament) {
                        $m->whereHas('gameweek', function ($g) use ($currentTournament) {
                            $g->where('tournament_id', $currentTournament->id);
                        });
                    })->with('match');
                }
            ]);
        }

        $users = $query->orderByDesc('predictions_sum_points_awarded')
            ->paginate(20);

        // Calculate Hit Rates
        $users->getCollection()->each(function ($user) {
            $played = $user->predictions->filter(function ($p) {
                return $p->match && !is_null($p->match->home_score) && !is_null($p->match->away_score);
            })->count();

            $hits = $user->predictions->filter(function ($p) {
                $m = $p->match;
                if (!$m || is_null($m->home_score) || is_null($m->away_score))
                    return false;

                $predDiff = $p->predicted_home - $p->predicted_away;
                $actualDiff = $m->home_score - $m->away_score;

                // Compare signs: -1 (Away), 0 (Draw), 1 (Home)
                return ($predDiff <=> 0) === ($actualDiff <=> 0);
            })->count();

            $user->predictions_played = $played;
            $user->predictions_hit = $hits;
            $user->hit_rate = $played > 0 ? round(($hits / $played) * 100) : 0;
        });

        // Fetch Gameweeks for the matrix
        if ($currentTournament) {
            $gameweeks = $currentTournament->gameweeks()
                ->orderBy('start_date', 'desc') // Changed to DESC
                ->with(['matches.predictions']) // Eager load for points calc
                ->get();
        } else {
            $gameweeks = collect();
        }

        // Calculate Gameweek Wins and High Scores
        $gameweekWins = []; // [userId => count]
        $roundWinners = []; // [gwId => ['score' => int, 'users' => [id, id]]]

        if ($currentTournament) {
            foreach ($gameweeks as $gw) {
                // Determine scores for this GW for ALL users
                $gwUserPoints = [];

                // Assuming 'completed' status is the source of truth for "Winning" a round.
                if ($gw->status !== 'completed')
                    continue;

                foreach ($gw->matches as $match) {
                    foreach ($match->predictions as $pred) {
                        if (!isset($gwUserPoints[$pred->user_id])) {
                            $gwUserPoints[$pred->user_id] = 0;
                        }
                        $gwUserPoints[$pred->user_id] += $pred->points_awarded;
                    }
                }

                if (empty($gwUserPoints))
                    continue;

                $maxPoints = max($gwUserPoints);

                // Track round info
                $roundWinners[$gw->id] = [
                    'score' => $maxPoints,
                    'users' => []
                ];

                // Only award/track if there are points > 0
                if ($maxPoints > 0) {
                    foreach ($gwUserPoints as $uId => $pts) {
                        if ($pts === $maxPoints) {
                            if (!isset($gameweekWins[$uId])) {
                                $gameweekWins[$uId] = 0;
                            }
                            $gameweekWins[$uId]++;

                            $roundWinners[$gw->id]['users'][] = $uId;
                        }
                    }
                }
            }
        }

        return view('leaderboard.index', compact('users', 'tournaments', 'currentTournament', 'gameweeks', 'gameweekWins', 'roundWinners'));
    }

    public function showRound(\App\Models\Gameweek $gameweek): View
    {
        $gameweek->load(['matches.predictions.user']);

        return view('leaderboard.round', compact('gameweek'));
    }

    public function liveTable(Request $request, \App\Services\ScoringService $scoringService)
    {
        $tournaments = \App\Models\Tournament::orderByDesc('created_at')->get();
        $currentTournament = $request->has('tournament_id')
            ? $tournaments->find($request->get('tournament_id'))
            : $tournaments->firstWhere('is_active', true) ?? $tournaments->first();

        // 1. Base Query (Same as Index)
        $query = User::withSum([
            'predictions' => function ($query) use ($currentTournament) {
                if ($currentTournament) {
                    $query->whereHas('match', function ($q) use ($currentTournament) {
                        $q->whereHas('gameweek', function ($gw) use ($currentTournament) {
                            $gw->where('tournament_id', $currentTournament->id);
                        });
                    });
                }
            }
        ], 'points_awarded');

        if ($currentTournament) {
            $query->whereHas('tournaments', function ($q) use ($currentTournament) {
                $q->where('tournaments.id', $currentTournament->id);
            });
            // Eager load info needed for stats + LIVE calculations
            $query->with([
                'predictions' => function ($q) use ($currentTournament) {
                    $q->whereHas('match', function ($m) use ($currentTournament) {
                        $m->whereHas('gameweek', function ($g) use ($currentTournament) {
                            $g->where('tournament_id', $currentTournament->id);
                        });
                    })->with('match'); // Need match info for scoring
                }
            ]);
        }

        $users = $query->get(); // Get ALL users to sort correctly in PHP

        // 2. Identify Active Matches (Started but NOT Completed)
        $activeMatches = collect();
        if ($currentTournament) {
            $activeMatches = \App\Models\GameMatch::whereHas('gameweek', function ($q) use ($currentTournament) {
                $q->where('tournament_id', $currentTournament->id);
            })
                ->where('start_time', '<=', now())
                ->where('status', '!=', 'completed')
                ->with(['predictions'])
                ->get();
        }

        // 3. Calculate Live Points
        $users->each(function ($user) use ($activeMatches, $scoringService) {
            // Basic Stats (Hit Rate etc - reuse logic)
            $played = $user->predictions->filter(function ($p) {
                return $p->match && !is_null($p->match->home_score) && !is_null($p->match->away_score);
            })->count();

            $hits = $user->predictions->filter(function ($p) {
                $m = $p->match;
                if (!$m || is_null($m->home_score) || is_null($m->away_score))
                    return false;
                $predDiff = $p->predicted_home - $p->predicted_away;
                $actualDiff = $m->home_score - $m->away_score;
                return ($predDiff <=> 0) === ($actualDiff <=> 0);
            })->count();

            $user->predictions_played = $played;
            $user->predictions_hit = $hits;
            $user->hit_rate = $played > 0 ? round(($hits / $played) * 100) : 0;

            // LIVE SCORE UPDATE
            $livePoints = 0;
            foreach ($activeMatches as $match) {
                // Must ensure match has a score to calculate against
                if (is_null($match->home_score) || is_null($match->away_score))
                    continue;

                $pred = $user->predictions->firstWhere('match_id', $match->id);
                if ($pred) {
                    // Calculate what the score WOULD be
                    $livePoints += $scoringService->calculatePredictionScore($match, $pred);
                }
            }

            // Add live points to the DB-stored 'points_awarded' sum
            // Note: points_awarded for active matches should be 0 in DB
            $user->predictions_sum_points_awarded += $livePoints;
        });

        // 4. Sort by New Total
        $users = $users->sortByDesc('predictions_sum_points_awarded')->values();

        // Pagination manually since we fetched all to sort
        $page = $request->get('page', 1);
        $perPage = 20;
        $paginatedUsers = new \Illuminate\Pagination\LengthAwarePaginator(
            $users->forPage($page, $perPage),
            $users->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Gameweek Wins (Just pass empty or calculate? - Keeping static for now as GW win for "live" gw is volatile)
        // We'll pass empty array for simplicity or fetch existing
        $gameweekWins = [];

        return view('leaderboard.partials.table', [
            'users' => $paginatedUsers,
            'gameweekWins' => $gameweekWins // Stats might effectively disappear in live view or we need to recalc them too
        ]);
    }
}
