<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- AI Pundit Section -->
            <div
                class="bg-gradient-to-r from-pl-purple to-[#2f0034] overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10 relative">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-pl-green blur-3xl opacity-20 rounded-full">
                </div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-pl-pink blur-3xl opacity-20 rounded-full">
                </div>

                <div class="p-8 relative z-10">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <!-- Bot Avatar -->
                        <div class="flex-shrink-0">
                            <div
                                class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center border-2 border-pl-green shadow-[0_0_15px_rgba(0,255,135,0.3)]">
                                <span class="text-3xl">🤖</span>
                            </div>
                        </div>

                        <!-- Message Bubble -->
                        <div class="flex-1 w-full text-center md:text-left">
                            <h3 class="text-pl-green font-bold text-sm uppercase tracking-widest mb-1">The AI Pundit
                                Says...</h3>
                            <div
                                class="bg-white/10 backdrop-blur-md rounded-tr-2xl rounded-br-2xl rounded-bl-2xl rounded-tl-sm p-5 border border-white/5 inline-block">
                                <p class="text-white text-lg font-medium italic leading-relaxed">
                                    "{{ $banter }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div
                    class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-lg sm:rounded-2xl border border-white/10 p-6">
                    <h3 class="text-white font-bold text-lg mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('predictions.index') }}"
                            class="block w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 px-4 rounded-lg transition border border-white/5 flex items-center justify-between group">
                            <span>🔮 Make Predictions</span>
                            <span class="text-pl-green group-hover:translate-x-1 transition">→</span>
                        </a>
                        <a href="{{ route('leaderboard.index') }}"
                            class="block w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 px-4 rounded-lg transition border border-white/5 flex items-center justify-between group">
                            <span>🏆 View Leaderboard</span>
                            <span class="text-pl-green group-hover:translate-x-1 transition">→</span>
                        </a>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div
                    class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-lg sm:rounded-2xl border border-white/10 p-6">
                    <h3 class="text-white font-bold text-lg mb-4">Your Season</h3>
                    <div class="flex items-center justify-between gap-4">
                        <div class="bg-black/20 rounded-xl p-4 flex-1 text-center border border-white/5">
                            <div class="text-xs text-gray-400 uppercase font-bold tracking-wider">Points</div>
                            <div class="text-2xl font-black text-pl-blue mt-1">
                                {{ Auth::user()->predictions()->sum('points_awarded') }}
                            </div>
                        </div>
                        <div class="bg-black/20 rounded-xl p-4 flex-1 text-center border border-white/5">
                            <div class="text-xs text-gray-400 uppercase font-bold tracking-wider">Leaderboard position
                            </div>
                            <div class="text-2xl font-black text-white mt-1">
                                #{{ $rank }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Matches Teaser -->
            @if(isset($upcomingGameweek) && $upcomingMatches->isNotEmpty())
                <div class="mt-8">
                    <a href="{{ route('pundit.show', $upcomingGameweek) }}" class="block group">
                        <div
                            class="bg-gradient-to-r from-zinc-900 to-black border border-white/10 rounded-2xl p-8 relative overflow-hidden hover:border-pl-pink/50 transition duration-300">
                            <!-- Decorative BG -->
                            <div class="absolute right-0 top-0 w-64 h-64 bg-pl-pink/10 blur-[100px] rounded-full"></div>

                            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                                <div class="text-center md:text-left">
                                    <span
                                        class="bg-pl-green text-black text-xs font-black uppercase tracking-widest px-2 py-1 rounded mb-3 inline-block">
                                        Just Released
                                    </span>
                                    <h3 class="text-3xl font-serif font-black text-white mb-2 leading-tight">
                                        Gameweek {{ str_replace('Gameweek ', '', $upcomingGameweek->name) }} Preview
                                    </h3>
                                    <p class="text-gray-400 max-w-xl">
                                        The AI Pundit has analyzed every tactic, tweet, and tea leaf. Read the full
                                        breakdown of all {{ $upcomingMatches->count() }} matches.
                                    </p>
                                </div>

                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center gap-2 bg-white text-pl-purple font-bold px-6 py-3 rounded-full hover:bg-pl-green hover:text-black transition transform group-hover:scale-105">
                                        Read Article <span class="text-xl">→</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                <div class="mt-8 p-8 rounded-xl bg-white/5 border border-white/10 text-center">
                    <p class="text-gray-400">No active gameweeks to preview.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>