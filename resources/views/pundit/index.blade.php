<x-app-layout>
    <div class="py-12 bg-[#f4f4f4] min-h-screen text-zinc-900 font-serif">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Newspaper Header -->
            <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap" rel="stylesheet">
            <div class="border-b-4 border-black mb-8 pb-4 text-center">
                <h1 class="text-6xl md:text-8xl font-black uppercase tracking-tighter mb-2"
                    style="font-family: 'UnifrakturMaguntia', cursive;">
                    The Pundit
                </h1>
                <div
                    class="flex justify-between border-t border-b border-zinc-300 py-1 mt-2 text-xs font-sans font-bold tracking-widest text-zinc-500 uppercase">
                    <span>{{ now()->format('l, F j, Y') }}</span>
                    <span>Bantersliga Daily</span>
                    <span>Est. 2024</span>
                </div>
            </div>

            @if($gameweeks->count() > 0)
                @php
                    $latest = $gameweeks->shift();
                    $summary = \Illuminate\Support\Facades\Cache::get("pundit_summary_{$latest->id}", [
                        'headline' => "Gameweek {$latest->name}: The Preview",
                        'subheadline' => "The football continues. Will your predictions hold up, or will you crumble under pressure?"
                    ]);
                @endphp

                <!-- Main Feature -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12 border-b border-zinc-300 pb-12">
                    <div class="lg:col-span-8">
                        <a href="{{ route('pundit.show', $latest) }}" class="group block">
                            <span class="text-pl-pink font-sans font-bold text-xs uppercase tracking-widest mb-2 block">
                                Editor's Pick
                            </span>
                            <div
                                class="mb-6 relative group cursor-pointer overflow-hidden rounded-lg shadow-2xl border-4 border-black">
                                @if($latest->image_path)
                                    <img src="{{ asset('storage/' . $latest->image_path) }}" alt="Gameweek Art"
                                        class="w-full h-96 object-cover transform transition duration-700 group-hover:scale-110">
                                @else
                                    <!-- Fallback Placeholder -->
                                    <div class="w-full h-96 bg-zinc-900 flex items-center justify-center">
                                        <span class="text-zinc-700 font-serif italic text-2xl">Visualizing Chaos...</span>
                                    </div>
                                @endif
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-60">
                                </div>
                            </div>

                            <div class="text-center max-w-4xl mx-auto px-4">
                                <h2
                                    class="text-5xl font-bold leading-none mb-4 group-hover:text-pl-pink transition-colors duration-200">
                                    {{ $summary['headline'] }}
                                </h2>
                                <p class="text-xl leading-relaxed text-zinc-600 mb-6 font-sans">
                                    {{ $summary['subheadline'] }}
                                </p>
                            </div>

                        </a>
                    </div>

                    <!-- Sidebar / Latest News -->
                    <div class="lg:col-span-4 border-l border-zinc-200 pl-8 flex flex-col justify-between">
                        <div>
                            <h3
                                class="font-sans font-bold text-lg uppercase border-b-2 border-pl-green pb-1 mb-4 inline-block">
                                In Brief
                            </h3>
                            <div class="space-y-6">
                                <div class="group">
                                    <h4 class="font-bold text-lg leading-tight mb-1 group-hover:underline">
                                        Why your predictions are doomed
                                    </h4>
                                    <p class="text-sm text-zinc-500 font-sans line-clamp-2">
                                        Our AI analysis suggests a 99% chance of tears this weekend.
                                    </p>
                                </div>
                                <div class="group">
                                    <h4 class="font-bold text-lg leading-tight mb-1 group-hover:underline">
                                        The art of the wrong outcome
                                    </h4>
                                    <p class="text-sm text-zinc-500 font-sans line-clamp-2">
                                        A tactical breakdown of how to lose points in stoppage time.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Ad Placeholder -->
                        <div class="bg-zinc-100 p-6 text-center mt-8 border border-zinc-200">
                            <span class="font-sans text-xs font-bold text-zinc-400 uppercase">Advertisement</span>
                            <p class="font-serif italic text-lg mt-2">"Bet with your head, not over it."</p>
                        </div>
                    </div>
                </div>

                <!-- Archive Grid -->
                @if($gameweeks->count() > 0)
                    <h3 class="font-sans font-bold text-xl uppercase border-b border-zinc-900 pb-2 mb-6">
                        Archive
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        @foreach($gameweeks as $gw)
                            @php
                                $gwSummary = \Illuminate\Support\Facades\Cache::get("pundit_summary_{$gw->id}", [
                                    'headline' => "Gameweek {$gw->name}",
                                    'subheadline' => "Detailed analysis of past performance."
                                ]);
                            @endphp
                            <a href="{{ route('pundit.show', $gw) }}" class="block group border-t border-zinc-200 pt-4">
                                @if($gw->image_path)
                                    <div class="mb-4 overflow-hidden rounded-sm border border-zinc-900 shadow-sm relative">
                                        <img src="{{ asset('storage/' . $gw->image_path) }}" alt="Gameweek Art"
                                            class="w-full h-48 object-cover transform transition duration-500 group-hover:scale-105 filter grayscale hover:grayscale-0">
                                        <div
                                            class="absolute top-0 right-0 bg-black text-white text-[10px] uppercase font-bold px-2 py-1">
                                            GW {{ $gw->name }}
                                        </div>
                                    </div>
                                @endif
                                <span class="text-zinc-400 font-sans text-xs font-bold uppercase mb-1 block">
                                    {{ $gw->start_date->format('M d, Y') }}
                                </span>
                                <h4 class="text-2xl font-bold leading-tight mb-2 group-hover:text-pl-pink transition-colors">
                                    {{ $gwSummary['headline'] }}
                                </h4>
                                <p class="text-sm text-zinc-600 font-sans line-clamp-3">
                                    {{ $gwSummary['subheadline'] }}
                                </p>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-12 font-sans">
                        {{ $gameweeks->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-24">
                    <h2 class="text-4xl font-bold text-zinc-400">No News Is Good News?</h2>
                    <p class="text-zinc-500 mt-4">Check back later for punditry.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>