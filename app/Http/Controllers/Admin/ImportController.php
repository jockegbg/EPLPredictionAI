<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gameweek;
use App\Models\Tournament;
use App\Models\GameMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function create()
    {
        $tournaments = Tournament::orderBy('created_at', 'desc')->get();
        return view('admin.import.create', compact('tournaments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'gameweek_number' => 'required|integer|min:1|max:38',
        ]);

        $gwNum = $request->gameweek_number;
        $tournamentId = $request->tournament_id;

        // 1. Fetch Fixtures from FPL
        $response = Http::get("https://fantasy.premierleague.com/api/fixtures/?event={$gwNum}");

        if ($response->failed()) {
            return back()->with('error', 'Failed to connect to FPL API.');
        }

        $fixtures = $response->json();

        if (empty($fixtures)) {
            return back()->with('error', "No fixtures found for Gameweek {$gwNum}.");
        }

        // 2. Load Mapping (Team ID -> Name)
        $map = [
            1 => 'Arsenal',
            2 => 'Aston Villa',
            3 => 'Bournemouth',
            4 => 'Brentford',
            5 => 'Brighton',
            6 => 'Burnley',
            7 => 'Chelsea',
            8 => 'Crystal Palace',
            9 => 'Everton',
            10 => 'Fulham',
            11 => 'Leeds',
            12 => 'Liverpool',
            13 => 'Man City',
            14 => 'Man Utd',
            15 => 'Newcastle',
            16 => "Nott'm Forest",
            17 => 'Southampton',
            18 => 'Spurs',
            19 => 'West Ham',
            20 => 'Wolves'
        ];

        // Dynamic Map Fetch
        $bootstrap = Http::get("https://fantasy.premierleague.com/api/bootstrap-static/");
        if ($bootstrap->successful()) {
            foreach ($bootstrap->json()['teams'] as $team) {
                // Map FPL name to our config name if necessary
                $name = $team['name'];
                if ($name == 'Manchester City')
                    $name = 'Man City';
                if ($name == 'Manchester United')
                    $name = 'Man Utd';
                if ($name == 'Tottenham Hotspur')
                    $name = 'Spurs';
                if ($name == 'Nottingham Forest')
                    $name = "Nott'm Forest";
                if ($name == 'Wolverhampton Wanderers')
                    $name = 'Wolves';
                if ($name == 'Brighton & Hove Albion')
                    $name = 'Brighton';
                if ($name == 'West Ham United')
                    $name = 'West Ham';
                if ($name == 'Newcastle United')
                    $name = 'Newcastle';
                if ($name == 'Leicester City')
                    $name = 'Leicester';
                if ($name == 'Ipswich Town')
                    $name = 'Ipswich';

                $map[$team['id']] = $name;
            }
        } else {
            // Fallback to local file if API fails
            $fplPath = base_path('fpl_data.json');
            if (file_exists($fplPath)) {
                $fplData = json_decode(file_get_contents($fplPath), true);
                foreach ($fplData['teams'] ?? [] as $t) {
                    $map[$t['id']] = $t['name'];
                }
            }
        }

        // 3. Create/Update Gameweek
        $gameweek = Gameweek::firstOrCreate(
            [
                'name' => "Gameweek {$gwNum}",
                'tournament_id' => $tournamentId
            ],
            [
                'start_date' => Carbon::parse($fixtures[0]['kickoff_time'])->subHours(1), // Rough buffer
                'end_date' => Carbon::parse($fixtures[count($fixtures) - 1]['kickoff_time'])->addHours(4),
                'status' => 'upcoming'
            ]
        );

        $count = 0;
        foreach ($fixtures as $fix) {
            $homeId = $fix['team_h'];
            $awayId = $fix['team_a'];
            $kickoff = Carbon::parse($fix['kickoff_time']);

            // Map IDs to Names
            $homeTeam = $map[$homeId] ?? "Unknown ({$homeId})";
            $awayTeam = $map[$awayId] ?? "Unknown ({$awayId})";

            // Create Match
            GameMatch::updateOrCreate(
                [
                    'gameweek_id' => $gameweek->id,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                ],
                [
                    'start_time' => $kickoff,
                    'status' => $fix['finished'] ? 'completed' : 'upcoming',
                    'home_score' => $fix['finished'] ? $fix['team_h_score'] : null,
                    'away_score' => $fix['finished'] ? $fix['team_a_score'] : null,
                ]
            );
            $count++;
        }

        return redirect()->route('admin.gameweeks.index')
            ->with('success', "Imported {$count} matches into {$gameweek->name}.");
    }
}
