<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Back Link -->
            <a href="{{ route('pundit.index') }}"
                class="inline-flex items-center gap-2 text-zinc-500 hover:text-white mb-8 transition">
                <span>←</span> Back to Articles
            </a>

            <!-- Main Container - Light Mode for Newspaper Feel -->
            <div class="bg-[#fcfbf9] border border-zinc-200 shadow-2xl rounded-sm overflow-hidden relative">

                <!-- Telegraph Style Header -->
                <div class="text-center border-b-4 border-black pb-8 mb-8 pt-12 px-8">
                    <!-- "The Pundit's Corner" -->
                    <div
                        class="inline-block bg-black text-white text-xs font-bold px-3 py-1 uppercase tracking-widest mb-4 transform -rotate-1 shadow-[5px_5px_0px_0px_rgba(0,0,0,0.3)]">
                        The Pundit's Corner
                    </div>

                    <h1 class="text-4xl md:text-6xl font-serif font-black mb-4 leading-tight text-zinc-900">
                        {{ $summary['headline'] ?? "Gameweek {$gameweek->name}" }}
                    </h1>

                    <h2 class="text-xl md:text-2xl font-serif text-zinc-600 italic mb-6">
                        {{ $summary['subheadline'] ?? 'Analysis of the upcoming fixtures.' }}
                    </h2>

                    <div
                        class="flex items-center justify-center gap-4 text-sm font-bold uppercase tracking-wider text-zinc-400">
                        <span>By The Pundit</span>
                        <span>•</span>
                        <span>{{ $gameweek->start_date->format('F jS, Y') }}</span>
                        <span>•</span>
                        <span class="text-pl-pink">Humour</span>
                    </div>
                </div>

                <!-- Gameweek Image (if available) -->
                @if($gameweek && $gameweek->image_path)
                    <figure class="mb-10 text-center px-4">
                        <img src="{{ Str::startsWith($gameweek->image_path, 'http') ? $gameweek->image_path : asset('storage/' . $gameweek->image_path) }}"
                            alt="Gameweek Art"
                            class="w-full max-w-3xl mx-auto h-auto rounded-sm shadow-xl border border-zinc-200">
                        <figcaption class="mt-2 text-xs text-zinc-500 font-serif italic text-center">
                            Fig 1. The expected chaos of the weekend.
                        </figcaption>
                    </figure>
                @endif

                <!-- Main Body - Telegraph Style -->
                <div class="prose prose-lg prose-zinc max-w-3xl mx-auto font-serif px-8 pb-16">



                    <!-- Intro Text -->
                    <p
                        class="text-xl text-zinc-900 font-serif border-l-4 border-pl-green pl-6 italic leading-relaxed mb-12">
                        "Another weekend, another chance for glory or total embarrassment. Here's exactly what's going
                        to happen (probably)."
                    </p>

                    <!-- Commentary Body -->
                    <div class="space-y-12">
                        @foreach($gameweek->matches as $m)
                            <article class="relative pl-0 md:pl-8 border-t border-zinc-200 pt-12 first:border-0 first:pt-0">
                                <!-- Heading -->
                                <h2 class="text-2xl font-bold text-zinc-900 mb-2 font-serif flex items-center gap-3">
                                    <span class="text-black">{{ $m->home_team }}</span>
                                    <span class="text-zinc-400 text-sm font-sans font-normal uppercase">vs</span>
                                    <span class="text-black">{{ $m->away_team }}</span>
                                </h2>

                                <div class="text-xs font-mono text-zinc-500 uppercase tracking-widest mb-6">
                                    📅 {{ $m->start_time->format('l, H:i') }}
                                </div>

                                <!-- AI Text -->
                                <div class="text-zinc-800 space-y-4 leading-relaxed">
                                    <p>{{ $m->ai_commentary['context'] ?? '' }}</p>
                                    <p>{{ $m->ai_commentary['analysis'] ?? '' }}</p>
                                </div>

                                <!-- Boxout -->
                                <div
                                    class="bg-zinc-100 border-l-4 border-pl-purple p-6 my-8 grid md:grid-cols-2 gap-6 shadow-sm">
                                    <!-- Verdict -->
                                    <div>
                                        <h5
                                            class="text-pl-purple text-sm font-black uppercase tracking-widest mb-3 flex items-center gap-2">
                                            <span>🤖</span> The AI Verdict
                                        </h5>
                                        <p class="text-zinc-900 italic font-medium text-lg border-l-2 border-zinc-300 pl-4">
                                            "{{ $m->ai_commentary['prediction'] ?? '' }}"
                                        </p>
                                        <div class="mt-3 text-zinc-500 font-mono text-xs uppercase pl-4">
                                            The AI Pundit predicts: <span
                                                class="text-black font-bold">{{ $m->ai_commentary['score_prediction'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- User Stats -->
                                    <div class="border-t md:border-t-0 md:border-l border-zinc-200 pt-4 md:pt-0 md:pl-6">
                                        <h5
                                            class="text-zinc-700 text-sm font-black uppercase tracking-widest mb-3 flex items-center gap-2">
                                            <span>👤</span> Your Prediction
                                        </h5>
                                        @if(isset($userPredictions[$m->id]))
                                            @php $p = $userPredictions[$m->id]; @endphp
                                            <div class="text-zinc-900 font-black text-3xl font-serif">
                                                {{ $p->predicted_home }} - {{ $p->predicted_away }}
                                            </div>
                                            @if($m->status === 'completed')
                                                <div class="mt-2 text-zinc-500 font-mono text-xs uppercase">
                                                    Result: <span class="text-pl-purple font-bold">{{ $m->home_score }} -
                                                        {{ $m->away_score }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <p class="text-zinc-400 italic text-sm">No prediction.</p>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Footer -->
                    <div class="mt-16 pt-8 border-t border-zinc-200 text-center">
                        <p class="text-zinc-400 text-sm italic">
                            The AI Pundit is a generative model trained on 10,000 hours of angry fan tweets and
                            questionable referee decisions.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>