<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ __('Make Predictions') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
            showModal: false, 
            showRulesModal: false,
            activeCommentary: null, 
            activeTeams: {},
            activeRules: { exact: 40, outcome: 10, penalty: 5 },
            isSaving: false,
            isLoadingCommentary: false,
            async fetchCommentary(matchId, home, away) {
                this.activeTeams = { home: home, away: away };
                this.activeCommentary = null;
                this.isLoadingCommentary = true;
                this.showModal = true;
                
                try {
                    const response = await fetch(`/pundit/match/${matchId}`);
                    const data = await response.json();
                    this.activeCommentary = data;
                } catch (error) {
                    this.activeCommentary = {
                        context: 'Connectivity issue with the pundit.',
                        analysis: 'Seems the line is dead.',
                        prediction: 'Try again later.'
                    };
                } finally {
                    this.isLoadingCommentary = false;
                }
            }
         }" x-init="$watch('showModal', value => {
            if (value) {
                history.pushState(null, null, '#pundit');
            } else {
                if (window.location.hash === '#pundit') {
                    history.back();
                }
            }
         })" @popstate.window="if (showModal) showModal = false"
        @keydown.escape.window="showModal = false; showRulesModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div
                    class="bg-pl-green/20 text-pl-green border border-pl-green/50 p-4 rounded mb-6 text-center backdrop-blur-sm font-bold shadow-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(!isset($activeGameweeks) || $activeGameweeks->isEmpty())
                <div
                    class="bg-white/10 backdrop-blur-md overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10 p-12 text-center">
                    <div class="text-white/50 text-xl font-medium">No active gameweeks found. Come back later!</div>
                </div>
            @else
                        <form action="{{ route('predictions.store') }}" method="POST" @submit="isSaving = true">
                            @csrf

                            @foreach($activeGameweeks as $gameweek)
                                            <div class="mb-12">
                                                <div class="flex items-center justify-between mb-6">
                                                    <div class="flex items-center gap-4">
                                                        <div class="h-8 w-1 bg-pl-green rounded-full shadow-[0_0_10px_#00ff87]"></div>
                                                        <div>
                                                            <h3 class="text-2xl font-bold text-white tracking-tight">{{ $gameweek->name }}</h3>
                                                            <p class="text-pl-blue font-medium text-sm">
                                                                {{ $gameweek->start_date->format('M d') }} -
                                                                {{ $gameweek->end_date->format('M d') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                            <button type="button" @click="showRulesModal = true; activeRules = {
                                                                    exact: {{ $gameweek->tournament->score_correct_score ?? 40 }},
                                                                    outcome: {{ $gameweek->tournament->score_correct_outcome ?? 10 }},
                                                                    penalty: {{ $gameweek->tournament->score_wrong_outcome_penalty ?? 0 }},
                                                                    double_down: {{ $gameweek->tournament->is_double_down_enabled ? 'true' : 'false' }},
                                                                    defence: {{ $gameweek->tournament->is_defence_enabled ? 'true' : 'false' }} 
                                                                }"
                                                class="text-white/60 hover:text-white flex items-center gap-1 text-sm font-bold bg-white/5 px-3 py-1 rounded-full hover:bg-white/10 transition">
                                                <span class="text-lg">ℹ️</span> Rules
                                            </button>
                                    </div>

                                    @php
                                        $gwMatchIds = $gameweek->matches->pluck('id');
                                        $userGwPreds = Auth::user()->predictions->whereIn('match_id', $gwMatchIds);
                                        $initDouble = $userGwPreds->where('is_double_points', true)->first()?->match_id ?? 'null';
                                        $initDefence = $userGwPreds->where('is_defence_chip', true)->first()?->match_id ?? 'null';
                                        
                                        // Group matches by date
                                        $matchesByDate = $gameweek->matches->sortBy('start_time')->groupBy(function($m) {
                                            return $m->start_time->format('l, M jS');
                                        });
                                    @endphp

                                    <div x-data="{ doubleId: {{ $initDouble }}, defenceId: {{ $initDefence }} }">
                                        @foreach($matchesByDate as $dateHeader => $dailyMatches)
                                            <h4 class="text-pl-green font-bold text-lg mb-4 mt-8 uppercase tracking-widest pl-2 border-l-4 border-pl-green/50">
                                                {{ $dateHeader }}
                                            </h4>
                                            
                                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                                @php
                                                    // Check if the double chip is already "spent" on a locked match in this gameweek
                                                    $gameweekDoubleLocked = false;
                                                    foreach ($gameweek->matches as $gwMatch) {
                                                        $gwPred = Auth::user()->predictions->where('match_id', $gwMatch->id)->first();
                                                        $gwMatchLocked = $gwMatch->start_time->isPast() || !is_null($gwMatch->home_score);
                                                        if ($gwPred && $gwPred->is_double_points && $gwMatchLocked) {
                                                            $gameweekDoubleLocked = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @foreach($dailyMatches as $match)
                                                    <div class="backdrop-blur-md rounded-2xl p-6 border shadow-xl transition-all duration-300 group relative"
                                                        :class="{
                                                                                    'bg-white/5 border-white/10 hover:bg-white/10 hover:border-pl-purple/50': doubleId != {{ $match->id }} && defenceId != {{ $match->id }},
                                                                                    'bg-pl-pink/10 border-pl-pink ring-1 ring-pl-pink shadow-[0_0_20px_rgba(255,40,130,0.4)] animate-pulse': doubleId == {{ $match->id }},
                                                                                    'bg-yellow-500/10 border-yellow-400 ring-1 ring-yellow-400 shadow-[0_0_20px_rgba(250,204,21,0.4)] animate-pulse': defenceId == {{ $match->id }}
                                                                                 }">

                                                        <!-- Match Time Badge -->
                                                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                            <span x-data x-text="new Date('{{ $match->start_time->toIso8601String() }}').toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"
                                                                title="{{ $match->start_time->format('l, M jS, Y h:i A') }}"
                                                                class="bg-[#2f0034] text-white/70 text-xs font-bold px-3 py-1 rounded-full border border-white/10 shadow-sm whitespace-nowrap cursor-help">
                                                                {{ $match->start_time->format('H:i') }}
                                                            </span>
                                                        </div>

                                                        <div class="mt-2 flex justify-between items-center mb-6">
                                                            <!-- Home Team -->
                                                            <div class="flex flex-col items-center w-1/3">
                                                                <div class="relative w-16 h-16 mb-2 flex items-center justify-center">
                                                                    <img src="{{ $match->home_team_logo }}" alt="{{ $match->home_team }}"
                                                                        class="w-14 h-14 object-contain drop-shadow-[0_4px_6px_rgba(0,0,0,0.5)] group-hover:scale-110 transition duration-300"
                                                                        onerror="this.style.display='none'">
                                                                </div>
                                                                <div class="font-bold text-white text-lg truncate w-full text-center leading-tight">
                                                                    {{ $match->home_team }}</div>
                                                            </div>

                                                            <!-- VS -->
                                                            <div class="text-center w-1/3 flex flex-col items-center justify-center">
                                                                <span class="text-white/20 font-black text-2xl italic">VS</span>
                                                            </div>

                                                            <!-- Away Team -->
                                                            <div class="flex flex-col items-center w-1/3">
                                                                <div class="relative w-16 h-16 mb-2 flex items-center justify-center">
                                                                    <img src="{{ $match->away_team_logo }}" alt="{{ $match->away_team }}"
                                                                        class="w-14 h-14 object-contain drop-shadow-[0_4px_6px_rgba(0,0,0,0.5)] group-hover:scale-110 transition duration-300"
                                                                        onerror="this.style.display='none'">
                                                                </div>
                                                                <div class="font-bold text-white text-lg truncate w-full text-center leading-tight">
                                                                    {{ $match->away_team }}</div>
                                                            </div>
                                                        </div>

                                                        <!-- Score Inputs -->
                                                        @php
                                                            $isLocked = $match->start_time->isPast() || !is_null($match->home_score);
                                                            $userPred = Auth::user()->predictions->where('match_id', $match->id)->first();
                                                        @endphp

                                                        <div
                                                            class="flex justify-center space-x-4 items-center bg-black/20 p-4 rounded-xl relative overflow-hidden mb-4 border border-white/5">
                                                            @if($isLocked)
                                                                <div
                                                                    class="absolute inset-0 bg-[#2f0034]/90 backdrop-blur-[2px] z-10 flex items-center justify-center">
                                                                    @php
                                                                        $isCompleted = $match->status === 'completed';
                                                                        $cashoutEnabled = $gameweek->tournament->is_cashout_enabled;
                                                                        $minutesSinceStart = $match->start_time->diffInMinutes(now());
                                                                        $inCashoutWindow = $minutesSinceStart >= 10 && !$isCompleted;
                                                                        $hasPred = !is_null($userPred);
                                                                    @endphp

                                                                    @if($hasPred && $userPred->cashed_out_at)
                                                                        <div class="text-center">
                                                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Cashed
                                                                                Out</span>
                                                                            <span
                                                                                class="text-3xl font-black text-pl-green drop-shadow-md">{{ $userPred->cashout_points }}
                                                                                pts</span>
                                                                        </div>
                                                                    @elseif($cashoutEnabled && $inCashoutWindow && $hasPred)
                                                                        <div class="text-center">
                                                                            <div class="text-white text-lg font-bold mb-1">
                                                                                {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
                                                                            </div>
                                                                            <button type="button"
                                                                                onclick="event.preventDefault(); if(confirm('Cash out now for 50% points?')) document.getElementById('cashout-form-{{ $match->id }}').submit();"
                                                                                class="bg-pl-green text-pl-purple font-black uppercase text-xs px-4 py-2 rounded-full shadow-lg hover:bg-white transition transform hover:scale-105">
                                                                                Cash Out 💰
                                                                            </button>

                                                                        </div>
                                                                    @elseif($isCompleted || !is_null($match->home_score))
                                                                        <div class="text-center">
                                                                            @if(!$isCompleted)
                                                                                <div class="flex items-center justify-center gap-2 mb-1">
                                                                                    <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                                                                    <span class="text-[10px] font-bold text-pl-green uppercase tracking-wider">
                                                                                        LIVE
                                                                                    </span>
                                                                                </div>
                                                                            @else
                                                                                <span class="text-[10px] font-bold text-white/50 uppercase tracking-wider block mb-1">
                                                                                    FT
                                                                                </span>
                                                                            @endif
                                                                            <span class="text-3xl font-black text-white drop-shadow-md">{{ $match->home_score ?? 0 }} -
                                                                                {{ $match->away_score ?? 0 }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span
                                                                            class="text-xs font-bold text-white/60 uppercase tracking-widest border border-white/20 px-3 py-1 rounded bg-black/30">Match
                                                                            Started</span>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            {{-- Use match ID as key to prevent index collisions in loops --}}
                                                            <input type="hidden" name="predictions[{{ $match->id }}][match_id]" value="{{ $match->id }}">

                                                            <input type="number" name="predictions[{{ $match->id }}][home]" min="0" placeholder="-"
                                                                class="w-14 h-14 text-center text-2xl font-bold bg-white/10 border-white/10 rounded-lg focus:ring-2 focus:ring-pl-green focus:border-pl-green text-white placeholder-white/30 disabled:opacity-50 transition-all"
                                                                value="{{ $userPred?->predicted_home ?? '' }}" {{ $isLocked ? 'disabled' : '' }}>

                                                            <span class="text-white/40 font-bold">:</span>

                                                            <input type="number" name="predictions[{{ $match->id }}][away]" min="0" placeholder="-"
                                                                class="w-14 h-14 text-center text-2xl font-bold bg-white/10 border-white/10 rounded-lg focus:ring-2 focus:ring-pl-green focus:border-pl-green text-white placeholder-white/30 disabled:opacity-50 transition-all"
                                                                value="{{ $userPred?->predicted_away ?? '' }}" {{ $isLocked ? 'disabled' : '' }}>
                                                        </div>

                                                        <!-- Helpers & Chips -->
                                                        <div class="flex items-center justify-between mt-4">
                                                            <!-- Help Button -->
                                                            <button type="button"
                                                                @click="fetchCommentary({{ $match->id }}, '{{ addslashes($match->home_team) }}', '{{ addslashes($match->away_team) }}')"
                                                                class="text-xs font-bold text-zinc-400 hover:text-pl-green flex items-center gap-1 transition-colors">
                                                                <span>🔮</span> Ask Pundit
                                                            </button>

                                                            <div class="flex items-center gap-3">
                                                                <!-- Double Chip Selector -->
                                                                @if(!$isLocked)
                                                                    @if(!$gameweekDoubleLocked || ($userPred && $userPred->is_double_points))
                                                                        <label class="inline-flex items-center cursor-pointer group relative" title="Double Points">
                                                                            <input type="radio" name="doubles[{{ $gameweek->id }}]" value="{{ $match->id }}"
                                                                                class="peer sr-only"
                                                                                @click="if(doubleId == {{ $match->id }}) { doubleId = null; $el.checked = false; } else { doubleId = {{ $match->id }}; if(defenceId == {{ $match->id }}) defenceId = null; }"
                                                                                {{ $userPred?->is_double_points ? 'checked' : '' }}>

                                                                            <div
                                                                                class="flex items-center gap-1 px-2 py-1 rounded-full border border-white/10 bg-white/5 peer-checked:bg-pl-pink peer-checked:border-pl-pink peer-checked:shadow-[0_0_15px_rgba(255,40,130,0.4)] transition-all duration-300">
                                                                                <div
                                                                                    class="w-3 h-3 rounded-full border-2 border-white/30 peer-checked:border-white peer-checked:bg-white transition-all">
                                                                                </div>
                                                                                <span
                                                                                    class="text-[10px] font-bold text-white/60 peer-checked:text-white uppercase tracking-wider group-hover:text-white transition-colors">
                                                                                    2x
                                                                                </span>
                                                                            </div>
                                                                        </label>
                                                                    @else
                                                                        <div class="opacity-30 cursor-not-allowed group" title="Two-times multiplier already used">
                                                                            <div
                                                                                class="flex items-center gap-1 px-2 py-1 rounded-full border border-white/10 bg-black/20">
                                                                                <div class="w-3 h-3 rounded-full border-2 border-white/30"></div>
                                                                                <span class="text-[10px] font-bold text-white/40 uppercase tracking-wider">2x</span>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @elseif($userPred?->is_double_points)
                                                                    <span
                                                                        class="text-[10px] font-bold bg-pl-pink text-white px-2 py-1 rounded-full shadow-lg shadow-pl-pink/30 uppercase tracking-wider animate-pulse">
                                                                        🔥 2x Active
                                                                    </span>
                                                                @endif

                                                                <!-- Defence Chip Selector -->
                                                                @if($gameweek->tournament->score_wrong_outcome_penalty > 0)
                                                                    @php
                                                                        $gameweekDefenceLocked = false;
                                                                        foreach ($gameweek->matches as $gwMatch) {
                                                                            $gwPred = Auth::user()->predictions->where('match_id', $gwMatch->id)->first();
                                                                            // Check if defence chip used on ANY match in this GW
                                                                            $gwMatchLocked = $gwMatch->start_time->isPast();
                                                                            if ($gwPred && $gwPred->is_defence_chip && $gwMatchLocked) {
                                                                                $gameweekDefenceLocked = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                    @endphp

                                                                    @if(!$isLocked)
                                                                        @if(!$gameweekDefenceLocked || ($userPred && $userPred->is_defence_chip))
                                                                            <label class="inline-flex items-center cursor-pointer group relative"
                                                                                title="Defence Chip (No Negative Points)">
                                                                                <input type="radio" name="defence[{{ $gameweek->id }}]" value="{{ $match->id }}"
                                                                                    class="peer sr-only"
                                                                                    @click="if(defenceId == {{ $match->id }}) { defenceId = null; $el.checked = false; } else { defenceId = {{ $match->id }}; if(doubleId == {{ $match->id }}) doubleId = null; }"
                                                                                    {{ $userPred?->is_defence_chip ? 'checked' : '' }}>

                                                                                <div
                                                                                    class="flex items-center gap-1 px-2 py-1 rounded-full border border-white/10 bg-white/5 peer-checked:bg-blue-600 peer-checked:border-blue-400 peer-checked:shadow-[0_0_15px_rgba(59,130,246,0.4)] transition-all duration-300">
                                                                                    <div
                                                                                        class="w-3 h-3 rounded-full border-2 border-white/30 peer-checked:border-white peer-checked:bg-white transition-all">
                                                                                    </div>
                                                                                    <span
                                                                                        class="text-[10px] font-bold text-white/60 peer-checked:text-white uppercase tracking-wider group-hover:text-white transition-colors">
                                                                                        🛡️
                                                                                    </span>
                                                                                </div>
                                                                            </label>
                                                                        @else
                                                                            <div class="opacity-30 cursor-not-allowed group" title="Defence Chip already used">
                                                                                <div
                                                                                    class="flex items-center gap-1 px-2 py-1 rounded-full border border-white/10 bg-black/20">
                                                                                    <div class="w-3 h-3 rounded-full border-2 border-white/30"></div>
                                                                                    <span class="text-[10px] font-bold text-white/40 uppercase tracking-wider">🛡️</span>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @elseif($userPred?->is_defence_chip)
                                                                        <span
                                                                            class="text-[10px] font-bold bg-blue-600 text-white px-2 py-1 rounded-full shadow-lg shadow-blue-600/30 uppercase tracking-wider">
                                                                            🛡️ Active
                                                                        </span>
                                                                    @endif
                                                                @endif
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                <div class="fixed bottom-0 left-0 w-full bg-[#18001c]/90 backdrop-blur-lg border-t border-white/10 p-4 z-50">
                    <div class="max-w-7xl mx-auto flex justify-center">
                        <button type="submit"
                            class="bg-pl-green hover:bg-white text-pl-purple font-black py-3 px-12 rounded-full shadow-[0_0_20px_rgba(0,255,135,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.4)] transform transition hover:scale-105 text-lg uppercase tracking-wide disabled:opacity-50 disabled:scale-100 disabled:cursor-not-allowed flex items-center gap-3"
                            :disabled="isSaving">
                            <span x-show="!isSaving">Save All Predictions</span>
                            <span x-show="isSaving" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-pl-purple" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
                <!-- Spacer for fixed footer -->
                <div class="h-24"></div>

                </form>

                {{-- Hidden Cashout Forms --}}
                @foreach($activeGameweeks as $gameweek)
                    @if($gameweek->tournament->is_cashout_enabled)
                        @foreach($gameweek->matches as $match)
                            <form id="cashout-form-{{ $match->id }}" action="{{ route('predictions.cashout', $match) }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        @endforeach
                    @endif
                @endforeach
            @endif
    </div>

    <!-- Pundit Modal -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <!-- Backdrop -->
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-zinc-900 border border-zinc-700 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                <div class="bg-zinc-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black text-white font-serif flex items-center gap-2">
                            <span class="bg-white/10 p-1 rounded">🔮</span> The AI Pundit says...
                        </h3>
                        <button @click="showModal = false" class="text-zinc-500 hover:text-white transition">✕</button>
                    </div>

                    <h4 class="text-center font-bold text-lg text-pl-green mb-6">
                        <span x-text="activeTeams.home"></span> vs <span x-text="activeTeams.away"></span>
                    </h4>

                    <!-- Loading State -->
                    <div x-show="isLoadingCommentary" class="py-12 flex flex-col items-center justify-center space-y-4">
                        <svg class="animate-spin h-10 w-10 text-pl-green" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-white/50 text-sm animate-pulse">Consulting the oracle...</p>
                    </div>

                    <!-- Content State -->
                    <div x-show="!isLoadingCommentary && activeCommentary"
                        class="space-y-4 text-gray-300 text-sm leading-relaxed">
                        <div class="bg-black/30 p-4 rounded-lg border-l-2 border-pl-purple">
                            <p x-text="activeCommentary?.context"></p>
                        </div>

                        <div class="bg-black/30 p-4 rounded-lg border-l-2 border-pl-blue">
                            <p x-text="activeCommentary?.analysis"></p>
                        </div>

                        <div class="bg-pl-green/10 p-4 rounded-lg border border-pl-green/30">
                            <strong
                                class="text-pl-green block mb-1 text-xs uppercase tracking-widest">Prediction</strong>
                            <p x-text="activeCommentary?.prediction" class="italic text-white"></p>
                        </div>

                        <!-- Score Prediction (Added as requested) -->
                        <div class="mt-4 text-center">
                            <span class="text-zinc-500 font-mono text-xs uppercase tracking-widest">The AI Pundit predicts:</span>
                            <span x-text="activeCommentary?.score_prediction" class="text-white font-bold text-lg ml-2"></span>
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-900 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-white/10 hover:bg-white/20 sm:mt-0 sm:w-auto transition"
                        @click="showModal = false">
                        Thanks, got it
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules Modal -->
    <div x-show="showRulesModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto"
        aria-labelledby="rules-modal-title" role="dialog" aria-modal="true">

        <div x-show="showRulesModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showRulesModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showRulesModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-zinc-900 border border-zinc-700 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                <div class="bg-zinc-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black text-white font-serif">Rules & Scoring</h3>
                        <button @click="showRulesModal = false"
                            class="text-zinc-500 hover:text-white transition">✕</button>
                    </div>

                    <div class="space-y-6 text-gray-300">
                        <!-- Points Breakdown -->
                        <div class="bg-black/30 p-4 rounded-xl border border-white/5">
                            <h4 class="text-pl-green font-bold uppercase tracking-wider text-xs mb-3">Points Breakdown
                            </h4>
                            <ul class="space-y-3">
                                <li class="flex justify-between items-center">
                                    <span>Correct Score</span>
                                    <span class="font-bold text-white bg-white/10 px-2 py-1 rounded"
                                        x-text="activeRules.exact + ' pts'">40 pts</span>
                                </li>
                                <!-- Only show diff if > 0 -->
                                <template x-if="activeRules.diff > 0">
                                    <li class="flex justify-between items-center">
                                        <span>Correct Goal Diff</span>
                                        <span class="font-bold text-white bg-white/10 px-2 py-1 rounded" x-text="activeRules.diff + ' pts'"></span>
                                    </li>
                                </template>
                                <li class="flex justify-between items-center">
                                    <span>Correct Outcome</span>
                                    <span class="font-bold text-white bg-white/10 px-2 py-1 rounded"
                                        x-text="activeRules.outcome + ' pts'">10 pts</span>
                                </li>
                                <!-- Only show penalty if > 0 -->
                                <template x-if="activeRules.penalty > 0">
                                    <li class="flex justify-between items-center text-red-400">
                                        <span>Incorrect Outcome</span>
                                        <span class="font-bold bg-red-500/10 px-2 py-1 rounded"
                                            x-text="'-' + activeRules.penalty + ' pts'">-5 pts</span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Power Chips -->
                        <div class="bg-black/30 p-4 rounded-xl border border-white/5">
                            <h4 class="text-pl-blue font-bold uppercase tracking-wider text-xs mb-3">Power Chips</h4>
                            <div class="space-y-4">
                                <template x-if="activeRules.double_down">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span
                                                class="bg-pl-pink text-white text-[10px] font-bold px-2 py-0.5 rounded-full">2x</span>
                                            <span class="font-bold text-white">Double Down</span>
                                        </div>
                                        <p class="text-sm text-gray-400">Doubles your points for one match. If you get it
                                            wrong, you lose double!</p>
                                    </div>
                                </template>
                                <template x-if="activeRules.defence">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span
                                                class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">🛡️</span>
                                            <span class="font-bold text-white">Defence Chip</span>
                                        </div>
                                        <p class="text-sm text-gray-400">Protects you from negative points if you get the
                                            outcome wrong.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>