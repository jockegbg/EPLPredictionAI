<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ __('Pundit\'s Corner') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-1 max-w-3xl mx-auto">
                @foreach($gameweeks as $gameweek)
                    <a href="{{ route('pundit.show', $gameweek) }}" class="block group">
                        <div
                            class="bg-zinc-900 border border-zinc-700/50 rounded-lg overflow-hidden hover:border-pl-pink/50 transition duration-300 shadow-xl relative">
                            <!-- Date Badge -->
                            <div
                                class="absolute top-4 right-4 bg-black/50 backdrop-blur text-xs font-mono text-zinc-400 px-2 py-1 rounded">
                                {{ $gameweek->start_date->format('M d, Y') }}
                            </div>

                            <div class="p-8">
                                <div class="text-pl-pink font-black uppercase tracking-widest text-xs mb-2">Editor's Choice
                                </div>
                                <h3
                                    class="text-3xl font-serif font-bold text-white mb-3 group-hover:text-pl-green transition">
                                    Gameweek {{ str_replace('Gameweek ', '', $gameweek->name) }}: The Preview
                                </h3>
                                <p class="text-zinc-400 leading-relaxed mb-6">
                                    The AI Pundit breaks down every match. Expect chaos, controversy, and at least one
                                    questionable penalty decision.
                                </p>
                                <div class="flex items-center gap-3 text-xs font-medium text-zinc-500">
                                    <span class="flex items-center gap-1"><span
                                            class="bg-zinc-800 rounded-full p-1">🤖</span> The AI Pundit</span>
                                    <span>•</span>
                                    <span>{{ $gameweek->matches_count ?? 10 }} Matches</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 max-w-3xl mx-auto">
                {{ $gameweeks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>