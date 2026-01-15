<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gameweek;
use App\Services\PunditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminPunditController extends Controller
{
    protected $punditService;

    public function __construct(PunditService $punditService)
    {
        $this->punditService = $punditService;
    }

    public function index(): View
    {
        $gameweeks = Gameweek::with('matches')
            ->orderBy('id', 'desc') // "just list them descending by title only" - usually ID desc or name desc, let's do ID desc as it implies chronological
            ->paginate(10);

        return view('admin.pundit.index', compact('gameweeks'));
    }

    public function regenerateImage(Gameweek $gameweek): RedirectResponse
    {
        try {
            $imageUrl = $this->punditService->generateGameweekImage($gameweek);
            if ($imageUrl) {
                $gameweek->update(['image_path' => $imageUrl]);
                return back()->with('success', 'Image regenerated successfully.');
            }
            return back()->with('error', 'Failed to generate image.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating image: ' . $e->getMessage());
        }
    }

    public function regenerateSummary(Gameweek $gameweek): RedirectResponse
    {
        try {
            $summary = $this->punditService->generateGameweekSummary($gameweek);
            $gameweek->update(['pundit_summary' => $summary]);
            return back()->with('success', 'Summary (Headers & Description) regenerated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating summary: ' . $e->getMessage());
        }
    }

    public function regenerateCommentary(Gameweek $gameweek): RedirectResponse
    {
        try {
            $matches = $gameweek->matches;
            $results = $this->punditService->generateBatchCommentary($matches);

            $count = 0;
            foreach ($matches as $match) {
                if (isset($results[$match->id])) {
                    $match->update(['ai_commentary' => $results[$match->id]]);
                    $count++;
                }
            }

            return back()->with('success', "Match content regenerated for {$count} matches.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating match content: ' . $e->getMessage());
        }
    }
}
