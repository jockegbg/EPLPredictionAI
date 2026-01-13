<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PunditService;

class PunditController extends Controller
{
    protected $punditService;

    public function __construct(PunditService $punditService)
    {
        $this->punditService = $punditService;
    }

    public function index()
    {
        $gameweeks = \App\Models\Gameweek::orderBy('start_date', 'desc')
            ->paginate(10);

        return view('pundit.index', compact('gameweeks'));
    }

    public function show(\App\Models\Gameweek $gameweek)
    {
        // Eager load matches for performance
        $gameweek->load([
            'matches' => function ($query) {
                $query->orderBy('start_time');
            }
        ]);

        // Generate commentary for each match
        foreach ($gameweek->matches as $match) {
            $match->ai_commentary = $this->punditService->generateExtendedCommentary($match);
        }

        return view('pundit.show', compact('gameweek'));
    }
}
