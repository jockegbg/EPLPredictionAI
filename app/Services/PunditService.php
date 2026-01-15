<?php

namespace App\Services;

use App\Models\GameMatch;
use Illuminate\Support\Facades\Log;

class PunditService
{
    protected $aiService;

    public function __construct(GenerativeAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateExtendedCommentary(GameMatch $match): array
    {
        // Wrapper for single match generation using the batch logic essentially (or keep legacy if needed)
        $home = $match->home_team;
        $away = $match->away_team;
        $date = $match->start_time->format('l F jS');
        $time = $match->start_time->format('H:i');

        $preds = [];
        foreach ($match->predictions as $p) {
            $user = $p->user->name ?? 'Unknown';
            $preds[] = "{$user} predicted {$p->predicted_home}-{$p->predicted_away}" . ($p->is_double_points ? " (Double Chip!)" : "");
        }
        $predictionContext = implode(", ", $preds);

        if ($match->status === 'completed') {
            $score = "{$match->home_score}-{$match->away_score}";
            $prompt = "Act as a ruthless, funny British football pundit writing for a high-end newspaper.
            The match {$home} vs {$away} FINISHED {$score}.
            Users' predictions: [{$predictionContext}].
            
            Write a 3-part review (Max 400 chars each) plus metadata:
            1. 'article_title': A clever, pun-filled headline for this match review.
            2. 'article_snippet': A short 1-sentence teaser.
            3. 'context': React to the {$score}.
            4. 'analysis': ROAST specific users who got it wrong. Short and punchy.
            5. 'prediction': Witty summary closing statement.
            6. 'score_prediction': Your predicted score (format: 'H-A', e.g. '2-1'). Since it's finished, claim you knew it all along.
            
            Return ONLY valid JSON: {\"article_title\": \"...\", \"article_snippet\": \"...\", \"context\": \"...\", \"analysis\": \"...\", \"prediction\": \"...\", \"score_prediction\": \"...\"}";
        } else {
            $prompt = "Act as a witty, cynical British football pundit writing for a high-end newspaper.
            Preview {$home} vs {$away} ({$date}, {$time}).
            Users' predictions: [{$predictionContext}].

            Write a 3-part preview (Max 400 chars each) plus metadata:
            1. 'article_title': A clever, pun-filled headline for this match preview.
            2. 'article_snippet': A short 1-sentence teaser.
            3. 'context': Set the scene concisely.
            4. 'analysis': Mock specific users' predictions.
            5. 'prediction': Your expert summary.
            6. 'score_prediction': Your predicted score (format: 'H-A', e.g. '2-1').
            
            Return ONLY valid JSON: {\"article_title\": \"...\", \"article_snippet\": \"...\", \"context\": \"...\", \"analysis\": \"...\", \"prediction\": \"...\", \"score_prediction\": \"...\"}";
        }

        $aiResponse = $this->aiService->generateText($prompt);

        if ($aiResponse) {
            $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
            $decoded = json_decode($cleanJson, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['context'])) {
                return $decoded;
            }
        }

        return $this->getFallback($match);
    }

    public function generateBatchCommentary($matches): array
    {
        $allResults = [];
        // Sequential processing to respect Free Tier Limits
        $chunks = $matches->chunk(1);

        foreach ($chunks as $chunk) {
            $promptParts = [];
            foreach ($chunk as $match) {
                $home = $match->home_team;
                $away = $match->away_team;
                $date = $match->start_time->format('l F jS H:i');

                $preds = [];
                if ($match->predictions) {
                    foreach ($match->predictions as $p) {
                        $user = $p->user->name ?? 'Unknown';
                        $preds[] = "{$user}: {$p->predicted_home}-{$p->predicted_away}";
                    }
                }
                $predictionContext = implode(", ", $preds);

                if ($match->status === 'completed') {
                    $score = "{$match->home_score}-{$match->away_score}";
                    $promptParts[] = "MATCH_ID_{$match->id}: {$home} vs {$away} (FINISHED {$score}). [{$predictionContext}]";
                } else {
                    $promptParts[] = "MATCH_ID_{$match->id}: {$home} vs {$away} (Preview: {$date}). [{$predictionContext}]";
                }
            }

            $matchesText = implode("\n\n", $promptParts);

            $prompt = "Act as a witty, ruthless British football pundit writing for a major newspaper.
            Process these matches:
            
            {$matchesText}
            
            For EACH match, provide valid JSON with:
            - article_title: Clever headline.
            - article_snippet: Teaser text.
            - context: Reaction/Scene setter.
            - analysis: User roast.
            - prediction: Summary.
            - score_prediction: 'H-A' (e.g. '3-1').

            Return JSON Object keyed by MATCH_ID (int):
            { \"{$chunk->first()->id}\": { \"article_title\": \"..\", \"article_snippet\": \"..\", \"context\": \"..\", \"analysis\": \"..\", \"prediction\": \"..\", \"score_prediction\": \"..\" } }";

            $aiResponse = $this->aiService->generateText($prompt);
            $parsed = false;

            if ($aiResponse) {
                $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
                $decoded = json_decode($cleanJson, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $allResults = $allResults + $decoded;
                    $parsed = true;
                } else {
                    Log::warning("Batch JSON Decode Error: " . json_last_error_msg());
                }
            }

            // IF FAILED: Use Fallback so cache is populated anyway
            if (!$parsed) {
                foreach ($chunk as $m) {
                    $allResults[$m->id] = $this->getFallback($m);
                }
                Log::warning("Used fallback for match ID: {$chunk->first()->id}");
            }

            // Limit Pause (5 seconds - optimized for paid tier/efficiency)
            if ($chunks->count() > 1) {
                sleep(2);
            }
        }

        return $allResults;
    }

    public function generateGameweekSummary(\App\Models\Gameweek $gameweek): array
    {
        $prompt = "Act as the Chief Football Writer for 'Bantersliga Daily'.
        Write a Front Page Headline and Intro for Gameweek: {$gameweek->name}.
        
        Context:
        - Start Date: {$gameweek->start_date->format('F jS')}
        - Key Matches: " . $gameweek->matches->take(3)->map(fn($m) => "{$m->home_team} vs {$m->away_team}")->implode(', ') . ".
        
        Return ONLY valid JSON:
        {
            \"headline\": \"A dramatic, clickbait-worthy main headline (Max 6 words)\",
            \"subheadline\": \"A punchy, witty subheading describing the upcoming chaos (Max 2 sentences)\"
        }";

        $aiResponse = $this->aiService->generateText($prompt);

        if ($aiResponse) {
            $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
            $decoded = json_decode($cleanJson, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['headline'])) {
                return $decoded;
            }
        }

        return [
            'headline' => "Gameweek {$gameweek->name} Preview",
            'subheadline' => "The football continues. Will your predictions hold up, or will you crumble under pressure?"
        ];
    }

    public function getFallback(GameMatch $match): array
    {
        return [
            'article_title' => "{$match->home_team} vs {$match->away_team}",
            'article_snippet' => "A clash of titans (or mediocrity).",
            'context' => "{$match->home_team} vs {$match->away_team}. Always a classic.",
            'analysis' => "The pundit is currently locked out of the studio. Try again later!",
            'prediction' => "Unclear.",
            'score_prediction' => "1-1"
        ];
    }

    public function generateGameweekImage(\App\Models\Gameweek $gameweek): ?string
    {
        $prompt = "Editorial newspaper cartoon illustration of an English football pundit, hand-drawn ink line art, bold sarcastic expression, slightly exaggerated facial features, black and white, rough sketch style, minimal shading, British sports newspaper aesthetic, confident and cynical mood, no colour or one muted accent, expressive eyebrows, clean white background.
        
        Context for specific drawing content:
        Gameweek {$gameweek->name}: {$gameweek->headline} - {$gameweek->subheadline}.
        
        Make it visually striking, witty, and chaotic.";

        // Use 1792x1024 for wide aspect ratio (approx 1.75:1, closest to requested 1.91:1 supported by DALL-E)
        $imageUrl = $this->aiService->generateImage($prompt, "1792x1024");

        if ($imageUrl) {
            try {
                $contents = file_get_contents($imageUrl);
                $filename = "gameweek_{$gameweek->id}_" . time() . ".png";
                $path = "gameweeks/{$filename}";

                // Store in public disk
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $contents);

                return $path;
            } catch (\Exception $e) {
                Log::error("Failed to download/save AI image: " . $e->getMessage());
            }
        }

        return null;
    }
    public function generateDashboardHumor(\App\Models\User $user, $contextData): array
    {
        $team = $user->favorite_team ?? 'Unknown Team';
        $rank = $contextData['rank'];
        $leader = $contextData['leader_name'];
        $upcoming = $contextData['upcoming_match'];

        $prompt = "Act as a ruthless, funny British football pundit. 
        User: {$user->name}. Favorite Team: {$team}. Rank: {$rank}.
        Leader: {$leader}. Next Match: {$upcoming}.

        Write a 3-part JSON response (Max 2 sentences each):
        1. 'greeting': A cheeky welcome message based on their rank.
        2. 'team_roast': A specific nasty comment about {$team}'s current form or history.
        3. 'prediction': A confident, unsolicited prediction about the next match {$upcoming}.

        Return ONLY valid JSON: {\"greeting\": \"...\", \"team_roast\": \"...\", \"prediction\": \"...\"}";

        $aiResponse = $this->aiService->generateText($prompt);

        if ($aiResponse) {
            $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
            $decoded = json_decode($cleanJson, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['greeting'])) {
                return $decoded;
            }
        }

        return [
            'greeting' => "Welcome back, {$user->name}. Still stuck at rank {$rank}, I see?",
            'team_roast' => "I'd roast {$team}, but their performance last week was joke enough.",
            'prediction' => "Football will be played. You will probably predict it wrong."
        ];
    }
}
