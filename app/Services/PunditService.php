<?php

namespace App\Services;

use App\Models\GameMatch;

class PunditService
{
    protected $aiService;

    public function __construct(GenerativeAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateExtendedCommentary(GameMatch $match): array
    {
        $home = $match->home_team;
        $away = $match->away_team;
        $date = $match->start_time->format('l F jS');
        $time = $match->start_time->format('H:i');

        // Try Generative AI First
        $prompt = "Act as a witty, slightly cynical British football pundit (like Roy Keane meets a stand-up comedian). 
        Write a short 3-part match preview for the Premier League game between {$home} and {$away} at {$time} on {$date}.
        
        Return ONLY valid JSON in this exact format:
        {
            \"context\": \"One sentence setting the scene.\",
            \"analysis\": \"Two sentences on form/tactics (humorous).\",
            \"prediction\": \"One sentence prediction with a specific score.\"
        }";

        $aiResponse = $this->aiService->generateText($prompt);

        if ($aiResponse) {
            // Clean up Markdown code blocks if present (Gemini often wraps JSON in ```json ... ```)
            $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
            $decoded = json_decode($cleanJson, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['context'], $decoded['analysis'], $decoded['prediction'])) {
                return $decoded;
            }
        }

        // FALLBACK: Static Templates
        // 1. Context / Setup
        $contexts = [
            "We head to {$home} for what promises to be a defining moment in the season. The fans are expectant, the managers are nervous, and the neutrals are just hoping for goals.",
            "This clash between {$home} and {$away} has all the ingredients of a classic. History suggests fireworks, but recent form suggests a cagey affair.",
            "{$home} welcome {$away} in a fixture that usually delivers drama. Both sides need points for very different reasons.",
        ];

        // 2. Tactical / Form Analysis
        $analysis = [
            "Looking at the tactical matchup, {$home} will try to dominate possession, but {$away}'s counter-attacking threat is real. It's going to come down to who blinks first.",
            "{$away} have been leaking goals lately, and {$home}'s attack is starting to click. If the visitors don't park the bus, this could get ugly quickly.",
            "It's an unstoppable force vs an immovable object. {$home} have been solid at the back, while {$away} are struggling to find the net. One goal might decide it.",
        ];

        // 3. Prediction / "Hot Take"
        $predictions = [
            "My algorithm predicts a 2-1 win for {$home}, but don't be surprised if VAR ruins the party. It usually does.",
            "I'm going for a shock {$away} victory. Sometimes logic goes out the window, and this feels like one of those days.",
            "A boring 0-0 draw written in the stars. Both teams will be too scared to lose. Avoid at all costs unless you need a nap.",
        ];

        return [
            'context' => $contexts[array_rand($contexts)],
            'analysis' => $analysis[array_rand($analysis)],
            'prediction' => $predictions[array_rand($predictions)],
        ];
    }
}
