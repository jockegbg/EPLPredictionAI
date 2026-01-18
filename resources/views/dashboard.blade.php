<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- AI Pundit Section -->
            <div x-data="{
                humor: {
                    greeting: 'The AI Pundit is warming up...',
                    team_roast: 'Analyzing your team\'s questionable decisions...',
                    prediction: 'Consulting the mystic football...'
                },
                loading: true,
                async init() {
                    try {
                        const response = await fetch(`{{ route('dashboard.pundit') }}?rank={{ $rank }}`);
                        const data = await response.json();
                        this.humor = data;
                    } catch (error) {
                        console.error('Pundit unavailable', error);
                        this.humor.greeting = 'The pundit has lost connection to the studio.';
                    } finally {
                        this.loading = false;
                    }
                }
            }"
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
                            <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center border-2 border-pl-green shadow-[0_0_15px_rgba(0,255,135,0.3)]"
                                :class="{ 'animate-pulse': loading }">
                                <span class="text-3xl">🤖</span>
                            </div>
                        </div>

                        <!-- Message Bubble -->
                        <div class="flex-1 w-full text-center md:text-left">
                            <h3 class="text-pl-green font-bold text-sm uppercase tracking-widest mb-1">The AI Pundit
                                Says...</h3>
                            <div class="space-y-4">
                                <!-- Greeting -->
                                <div class="bg-white/10 backdrop-blur-md rounded-tr-2xl rounded-br-2xl rounded-bl-2xl rounded-tl-sm p-5 border border-white/5 transition-opacity duration-500"
                                    :class="{ 'opacity-50': loading }">
                                    <p class="text-white text-lg font-medium italic leading-relaxed"
                                        x-text="'&quot;' + humor.greeting + '&quot;'">
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Team Roast -->
                                    <div class="bg-red-900/20 border border-red-500/20 rounded-xl p-4 relative overflow-hidden group transition-opacity duration-500 delay-100"
                                        :class="{ 'opacity-50': loading }">
                                        <div
                                            class="absolute -right-4 -top-4 w-16 h-16 bg-red-500/10 blur-xl rounded-full group-hover:bg-red-500/20 transition">
                                        </div>
                                        <h4
                                            class="text-red-400 font-bold text-xs uppercase mb-2 flex items-center gap-2">
                                            <span class="text-lg">🔥</span> On {{ Auth::user()->favorite_team }}
                                        </h4>
                                        <p class="text-gray-300 text-sm italic"
                                            x-text="'&quot;' + humor.team_roast + '&quot;'"></p>
                                    </div>

                                    <!-- Prediction -->
                                    <div class="bg-pl-blue/10 border border-pl-blue/20 rounded-xl p-4 relative overflow-hidden group transition-opacity duration-500 delay-200"
                                        :class="{ 'opacity-50': loading }">
                                        <div
                                            class="absolute -right-4 -top-4 w-16 h-16 bg-pl-blue/10 blur-xl rounded-full group-hover:bg-pl-blue/20 transition">
                                        </div>
                                        <h4
                                            class="text-pl-blue font-bold text-xs uppercase mb-2 flex items-center gap-2">
                                            <span class="text-lg">🎱</span> Mystic Ball
                                        </h4>
                                        <p class="text-gray-300 text-sm italic"
                                            x-text="'&quot;' + humor.prediction + '&quot;'"></p>
                                    </div>
                                </div>
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

                            <!-- Background Image if available -->
                            @if($upcomingGameweek->image_path)
                                <div class="absolute inset-0 z-0">
                                    <img src="{{ Str::startsWith($upcomingGameweek->image_path, 'http') ? $upcomingGameweek->image_path : asset('storage/' . $upcomingGameweek->image_path) }}"
                                        alt="Gameweek Art" class="w-full h-full object-cover opacity-40 mix-blend-overlay">
                                    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent"></div>
                                </div>
                            @else
                                <!-- Decorative BG Fallback -->
                                <div class="absolute right-0 top-0 w-64 h-64 bg-pl-pink/10 blur-[100px] rounded-full"></div>
                            @endif

                            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                                <div class="text-center md:text-left">
                                    <span
                                        class="bg-pl-green text-black text-xs font-black uppercase tracking-widest px-2 py-1 rounded-sm mb-3 inline-block">
                                        Just Released
                                    </span>
                                    <h3 class="text-3xl font-serif font-black text-white mb-2 leading-tight">
                                        {{ $upcomingGameweek->pundit_summary['headline'] ?? "Gameweek " . $upcomingGameweek->name . " Preview" }}
                                    </h3>
                                    <p class="text-gray-300 max-w-xl font-medium drop-shadow-md">
                                        {{ $upcomingGameweek->pundit_summary['subheadline'] ?? "The AI Pundit has analyzed every tactic, tweet, and tea leaf. Read the full breakdown of all " . $upcomingMatches->count() . " matches." }}
                                    </p>
                                </div>

                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center gap-2 bg-white text-pl-purple font-bold px-6 py-3 rounded-full hover:bg-pl-green hover:text-black transition transform group-hover:scale-105 shadow-lg">
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