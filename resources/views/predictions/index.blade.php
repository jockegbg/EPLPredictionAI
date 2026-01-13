<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ __('Make Predictions') }}
        </h2>
    </x-slot>

    <div class="py-12" 
         x-data="{ showModal: false, activeCommentary: {}, activeTeams: {} }"
         x-init="$watch('showModal', value => {
            if (value) {
                history.pushState(null, null, '#pundit');
            } else {
                if (window.location.hash === '#pundit') {
                    history.back();
                }
            }
         })"
         @popstate.window="if (showModal) showModal = false"
         @keydown.escape.window="showModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-pl-green/20 text-pl-green border border-pl-green/50 p-4 rounded mb-6 text-center backdrop-blur-sm font-bold shadow-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(!isset($activeGameweeks) || $activeGameweeks->isEmpty())
                <div class="bg-white/10 backdrop-blur-md overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10 p-12 text-center">
                    <div class="text-white/50 text-xl font-medium">No active gameweeks found. Come back later!</div>
                </div>
            @else
                <form action="{{ route('predictions.store') }}" method="POST">
                    @csrf
                    
                    @foreach($activeGameweeks as $gameweek)
                        <div class="mb-12">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="h-8 w-1 bg-pl-green rounded-full shadow-[0_0_10px_#00ff87]"></div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white tracking-tight">{{ $gameweek->name }}</h3>
                                    <p class="text-pl-blue font-medium text-sm">
                                        {{ $gameweek->start_date->format('M d') }} - {{ $gameweek->end_date->format('M d') }}
                                        <span class="text-white/40 mx-2">•</span>
                                        Predict the scores. Exact: <span class="text-white">40pts</span>. Outcome: <span class="text-white">10pts</span>.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                @php
                                    // Check if the double chip is already "spent" on a locked match in this gameweek
                                    $gameweekDoubleLocked = false;
                                    foreach($gameweek->matches as $gwMatch) {
                                        $gwPred = Auth::user()->predictions->where('match_id', $gwMatch->id)->first();
                                        $gwMatchLocked = $gwMatch->start_time->isPast() || !is_null($gwMatch->home_score);
                                        if ($gwPred && $gwPred->is_double_points && $gwMatchLocked) {
                                            $gameweekDoubleLocked = true;
                                            break;
                                        }
                                    }
                                @endphp

                                @foreach($gameweek->matches as $match)
                                    <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-xl hover:bg-white/10 hover:border-pl-purple/50 transition duration-300 group relative">
                                        
                                        <!-- Match Time Badge -->
                                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                            <span class="bg-[#2f0034] text-white/70 text-xs font-bold px-3 py-1 rounded-full border border-white/10 shadow-sm whitespace-nowrap">
                                                {{ $match->start_time->format('D M d, H:i') }}
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
                                                <div class="font-bold text-white text-lg truncate w-full text-center leading-tight">{{ $match->home_team }}</div>
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
                                                <div class="font-bold text-white text-lg truncate w-full text-center leading-tight">{{ $match->away_team }}</div>
                                            </div>
                                        </div>

                                        <!-- Score Inputs -->
                                        @php
                                            $isLocked = $match->start_time->isPast() || !is_null($match->home_score);
                                            $userPred = Auth::user()->predictions->where('match_id', $match->id)->first();
                                        @endphp

                                        <div class="flex justify-center space-x-4 items-center bg-black/20 p-4 rounded-xl relative overflow-hidden mb-4 border border-white/5">
                                            @if($isLocked)
                                                <div class="absolute inset-0 bg-[#2f0034]/90 backdrop-blur-[2px] z-10 flex items-center justify-center">
                                                    @if(!is_null($match->home_score))
                                                        <div class="text-center">
                                                            <span class="text-[10px] font-bold text-pl-green uppercase tracking-wider block mb-1">Final Score</span>
                                                            <span class="text-3xl font-black text-white drop-shadow-md">{{ $match->home_score }} - {{ $match->away_score }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-xs font-bold text-white/60 uppercase tracking-widest border border-white/20 px-3 py-1 rounded bg-black/30">Match Started</span>
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
                                                @click="showModal = true; activeCommentary = {{ json_encode($match->ai_commentary) }}; activeTeams = { home: {{ json_encode($match->home_team) }}, away: {{ json_encode($match->away_team) }} }"
                                                class="text-xs font-bold text-zinc-400 hover:text-pl-green flex items-center gap-1 transition-colors">
                                                <span>🔮</span> Ask Pundit
                                            </button>

                                            <!-- Double Chip Selector -->
                                            @if(!$isLocked)
                                                @if(!$gameweekDoubleLocked)
                                                    <label class="inline-flex items-center cursor-pointer group relative">
                                                        <input type="radio" name="doubles[{{ $gameweek->id }}]" value="{{ $match->id }}" 
                                                            class="peer sr-only"
                                                            {{ $userPred?->is_double_points ? 'checked' : '' }}>
                                                        
                                                        <div class="flex items-center gap-1 px-2 py-1 rounded-full border border-white/10 bg-white/5 peer-checked:bg-pl-pink peer-checked:border-pl-pink peer-checked:shadow-[0_0_15px_rgba(255,40,130,0.4)] transition-all duration-300">
                                                            <div class="w-3 h-3 rounded-full border-2 border-white/30 peer-checked:border-white peer-checked:bg-white transition-all"></div>
                                                            <span class="text-[10px] font-bold text-white/60 peer-checked:text-white uppercase tracking-wider group-hover:text-white transition-colors">
                                                                2x
                                                            </span>
                                                        </div>
                                                    </label>
                                                @else
                                                    <!-- Chip is locked on another match -->
                                                    <div class="opacity-30 cursor-not-allowed group" title="Two-times multiplier already used on a locked match">
                                                        <div class="flex items-center gap-1 px-2 py-1 rounded-full border border-white/10 bg-black/20">
                                                            <div class="w-3 h-3 rounded-full border-2 border-white/30"></div>
                                                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-wider">
                                                                2x Locked
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif($userPred?->is_double_points)
                                                 <span class="text-[10px] font-bold bg-pl-pink text-white px-2 py-1 rounded-full shadow-lg shadow-pl-pink/30 uppercase tracking-wider animate-pulse">
                                                    🔥 2x Active
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="fixed bottom-0 left-0 w-full bg-[#18001c]/90 backdrop-blur-lg border-t border-white/10 p-4 z-50">
                        <div class="max-w-7xl mx-auto flex justify-center">
                            <button type="submit"
                                class="bg-pl-green hover:bg-white text-pl-purple font-black py-3 px-12 rounded-full shadow-[0_0_20px_rgba(0,255,135,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.4)] transform transition hover:scale-105 text-lg uppercase tracking-wide">
                                Save All Predictions
                            </button>
                        </div>
                    </div>
                    <!-- Spacer for fixed footer -->
                    <div class="h-24"></div>

                </form>
            @endif
        </div>

        <!-- Pundit Modal -->
        <div x-show="showModal" 
             style="display: none;"
             class="fixed inset-0 z-[100] overflow-y-auto" 
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <!-- Backdrop -->
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" 
                 @click="showModal = false"></div>
    
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
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

                        <div class="space-y-4 text-gray-300 text-sm leading-relaxed">
                             <div class="bg-black/30 p-4 rounded-lg border-l-2 border-pl-purple">
                                <p x-text="activeCommentary.context"></p>
                             </div>
                             
                             <div class="bg-black/30 p-4 rounded-lg border-l-2 border-pl-blue">
                                <p x-text="activeCommentary.analysis"></p>
                             </div>

                             <div class="bg-pl-green/10 p-4 rounded-lg border border-pl-green/30">
                                <strong class="text-pl-green block mb-1 text-xs uppercase tracking-widest">Prediction</strong>
                                <p x-text="activeCommentary.prediction" class="italic text-white"></p>
                             </div>
                        </div>
                    </div>
                    
                    <div class="bg-zinc-900 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-white/10 hover:bg-white/20 sm:mt-0 sm:w-auto transition" @click="showModal = false">
                            Thanks, got it
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>