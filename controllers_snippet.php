public function matchCommentary(\App\Models\GameMatch $match)
{
$cacheKey = "match_commentary_{$match->id}";

// Cache for 24 hours (or until cleared manually)
$commentary = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDay(), function () use ($match) {
return $this->punditService->generateExtendedCommentary($match);
});

return response()->json($commentary);
}