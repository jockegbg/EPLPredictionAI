<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Back Link -->
            <a href="{{ route('pundit.index') }}"
                class="inline-flex items-center gap-2 text-zinc-500 hover:text-white mb-8 transition">
                <span>←</span> Back to Articles
            </a>

            <div class="bg-zinc-900 border border-zinc-800 shadow-2xl rounded-sm overflow-hidden relative">
                <!-- Article Header / Newspaper Style -->
                <div class="bg-zinc-100 p-8 md:p-12 text-center border-b-4 border-pl-pink relative overflow-hidden">
                    <div
                        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-pl-purple via-pl-pink to-pl-green">
                    </div>

                    <div
                        class="inline-block bg-black text-white text-xs font-black px-3 py-1 uppercase tracking-widest mb-4 transform -rotate-2">
                        The Pundit's Corner
                    </div>

                    <h1 class="text-4xl md:text-6xl font-black text-black font-serif mb-6 leading-tight">
                        Gameweek {{ str_replace('Gameweek ', '', $gameweek->name) }} Preview: <br />
                        <span class="text-pl-purple italic">Chaos Incoming?</span>
                    </h1>

                    <div class="flex items-center justify-center gap-3 text-zinc-600 text-sm font-medium">
                        <span class="flex items-center gap-1">
                            <span class="bg-gray-200 rounded-full p-1">🤖</span> By <strong>The AI Pundit</strong>
                        </span>
                        <span>•</span>
                        <span>{{ now()->format('F j, Y') }}</span>
                        <span>•</span>
                        <span>{{ $gameweek->matches->count() }} Matches</span>
                    </div>
                </div>

                <!-- Article Body -->
                <div class="p-8 md:p-16 space-y-16 bg-[#1a1a1a]">

                    <p class="text-2xl text-gray-300 font-serif border-l-4 border-pl-green pl-6 italic leading-relaxed">
                        "Another weekend, another chance for glory or total embarrassment. Here's exactly what's going
                        to happen (probably)."
                    </p>

                    <div class="space-y-16">
                        @foreach($gameweek->matches as $match)
                            <article class="relative pl-0 md:pl-8 border-t border-zinc-800 pt-12 first:border-0 first:pt-0">
                                <!-- Match Headline -->
                                <h2
                                    class="text-3xl font-bold text-white mb-4 font-serif group flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                                    <span class="text-pl-pink">{{ $match->home_team }}</span>
                                    <span
                                        class="text-sm text-zinc-500 font-sans font-normal uppercase tracking-wider">vs</span>
                                    <span class="text-pl-blue">{{ $match->away_team }}</span>
                                </h2>

                                <div
                                    class="flex items-center gap-4 mb-8 text-xs font-mono text-zinc-500 uppercase tracking-widest bg-black/20 inline-block px-3 py-1 rounded">
                                    <span>📅 {{ $match->start_time->format('l, H:i') }}</span>
                                </div>

                                <!-- The Commentary -->
                                <div class="prose prose-invert prose-lg max-w-none space-y-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ $match->ai_commentary['context'] }}
                                    </p>
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ $match->ai_commentary['analysis'] }}
                                    </p>
                                    <div class="bg-white/5 border border-white/10 p-6 rounded-lg my-6">
                                        <h5 class="text-pl-green text-sm font-bold uppercase tracking-widest mb-2">The
                                            Verdict</h5>
                                        <p class="text-white italic font-medium">
                                            "{{ $match->ai_commentary['prediction'] }}"
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Signature -->
                    <div class="mt-16 pt-8 border-t border-zinc-800 text-center">
                        <p class="text-zinc-500 text-sm italic">
                            The AI Pundit is a generative model trained on 10,000 hours of angry fan tweets and
                            questionable referee decisions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>