<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ __('Manage Gameweeks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-lg font-bold text-white">Manage Gameweeks</h3>
                            <form method="GET" action="{{ route('admin.gameweeks.index') }}">
                                <select name="tournament_id" onchange="this.form.submit()"
                                    class="bg-white/10 text-white border-white/20 rounded-md shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm backdrop-blur-md cursor-pointer">
                                    <option value="" class="bg-slate-800 text-white">All Tournaments</option>
                                    @foreach($allTournamentsList as $tList)
                                        <option value="{{ $tList->id }}" class="bg-slate-800 text-white" {{ request('tournament_id') == $tList->id ? 'selected' : '' }}>
                                            {{ $tList->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.import.create') }}"
                                class="bg-white hover:bg-gray-100 text-zinc-900 font-bold py-2 px-4 rounded-full shadow-lg border border-zinc-200 transition transform hover:scale-105 text-center text-xs uppercase tracking-widest">
                                Import FPL
                            </a>
                            <a href="{{ route('admin.gameweeks.create', ['tournament_id' => request('tournament_id')]) }}"
                                class="bg-white hover:bg-gray-100 text-zinc-900 font-bold py-2 px-4 rounded-full shadow-lg border border-zinc-200 transition transform hover:scale-105 text-center text-xs uppercase tracking-widest">
                                + Gameweek
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-pl-green/20 text-pl-green border border-pl-green/50 p-4 rounded mb-8 font-bold text-sm shadow-lg backdrop-blur-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    @foreach($activeTournaments as $tournament)
                        <div class="mb-12 bg-black/20 rounded-xl border border-white/5 overflow-hidden">
                            <!-- Tournament Header -->
                            <div class="px-6 py-4 bg-[#2f0034] border-b border-white/10 flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-black text-white tracking-tight">{{ $tournament->name }}</h3>
                                    <span class="text-[10px] uppercase font-bold tracking-wider {{ $tournament->is_active ? 'text-pl-green' : 'text-gray-500' }}">
                                        {{ $tournament->is_active ? 'Active Tournament' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="text-white/30 text-xs font-mono">
                                    ID: {{ $tournament->id }}
                                </div>
                            </div>

                            @if($tournament->gameweeks->isEmpty())
                                <div class="p-8 text-center text-white/30 italic text-sm">
                                    No gameweeks found in this tournament.
                                    <a href="{{ route('admin.gameweeks.create', ['tournament_id' => $tournament->id]) }}" class="text-pl-green underline hover:text-white ml-2">Create one?</a>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-white/10">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider bg-white/5">Gameweek</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider bg-white/5">Dates</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider bg-white/5">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider bg-white/5">Actions</th>
                                            </tr>
                                        </thead>
                                            @foreach($tournament->gameweeks as $gw)
                                                <tbody x-data="{ expanded: false }" class="border-b border-white/5">
                                                    <tr class="hover:bg-white/5 transition duration-150">
                                                        <!-- Main Row -->
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="flex items-center gap-3">
                                                                <button @click="expanded = !expanded" class="text-pl-green hover:text-white transition focus:outline-none p-1 rounded hover:bg-white/10">
                                                                    <svg x-show="!expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                    <svg x-show="expanded" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                                </button>
                                                                <span class="text-white font-bold">{{ $gw->name }}</span>
                                                                @if($gw->is_custom)
                                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">Custom</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                                            {{ $gw->start_date->format('M d') }} - {{ $gw->end_date->format('M d') }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 py-0.5 inline-flex text-[10px] leading-4 font-bold rounded-full uppercase tracking-wider
                                                                {{ $gw->status === 'active' ? 'bg-pl-green text-pl-purple' :
                                                                ($gw->status === 'completed' ? 'bg-white/10 text-white border border-white/20' : 'bg-blue-600/20 text-blue-300 border border-blue-600/30') }}">
                                                                {{ ucfirst($gw->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-4">
                                                            <a href="{{ route('admin.gameweeks.edit', $gw) }}" class="text-gray-400 hover:text-white transition">Edit</a>
                                                            <a href="{{ route('admin.matches.create', $gw) }}" class="text-pl-green hover:text-white transition">Add Match</a>
                                                            
                                                            <form action="{{ route('admin.gameweeks.recalculate', $gw) }}" method="POST" onsubmit="return confirm('Recalculate points for all completed matches?');">
                                                                @csrf
                                                                <button type="submit" class="text-blue-400 hover:text-white transition" title="Recalculate Scores">⟳</button>
                                                            </form>

                                                            <form action="{{ route('admin.gameweeks.generate-punditry', $gw) }}" method="POST" onsubmit="return confirm('Force generate AI Pundit content? This may take simple time.');">
                                                                @csrf
                                                                <button type="submit" class="text-pl-green hover:text-white transition" title="Generate Pundit Content">🤖</button>
                                                            </form>
                                                            
                                                            <form id="delete-gameweek-{{ $gw->id }}" method="POST" action="{{ route('admin.gameweeks.destroy', $gw) }}" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <button type="button" 
                                                                    onclick="if(confirm('Are you sure?')) { document.getElementById('delete-gameweek-{{ $gw->id }}').submit(); }"
                                                                    class="text-pl-pink hover:text-white transition cursor-pointer">
                                                                Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    
                                                    <!-- Expanded Matches Row -->
                                                     <tr x-show="expanded" style="display: none;" class="bg-black/40 shadow-inner">
                                                        <td colspan="4" class="px-4 py-4">
                                                            <div class="rounded-lg overflow-hidden border border-white/5 bg-[#18001c]">
                                                                 <div class="px-4 py-2 bg-white/5 border-b border-white/5 text-[10px] font-bold text-white/40 uppercase tracking-widest flex justify-between">
                                                                    <span>Matches</span>
                                                                    <span>{{ $gw->matches->count() }} Found</span>
                                                                 </div>

                                                                 @if($gw->matches->count() > 0)
                                                                     <ul class="divide-y divide-white/5">
                                                                        @foreach($gw->matches as $match)
                                                                            <li class="p-3 hover:bg-white/5 transition" x-data="{ isFinal: '{{ $match->status }}' === 'completed' }">
                                                                                <form action="{{ route('admin.matches.update', $match) }}" method="POST" class="flex items-center justify-between gap-4">
                                                                                    @csrf @method('PUT')
                                                                                    <input type="hidden" name="home_team" value="{{ $match->home_team }}">
                                                                                    <input type="hidden" name="away_team" value="{{ $match->away_team }}">
                                                                                    <input type="hidden" name="start_time" value="{{ $match->start_time }}">
                                                                                    <input type="hidden" name="status" :value="isFinal ? 'completed' : 'scheduled'">

                                                                                    <!-- Teams -->
                                                                                    <div class="flex items-center flex-1 justify-center gap-4">
                                                                                        <span class="text-right text-sm font-medium text-white w-32 truncate">{{ $match->home_team }}</span>
                                                                                        
                                                                                        <div class="flex items-center gap-2">
                                                                                            <input type="number" name="home_score" value="{{ $match->home_score }}" class="w-16 h-8 text-center bg-white/10 border-white/10 rounded text-sm text-white focus:border-pl-green focus:ring-pl-green" placeholder="-">
                                                                                            <span class="text-white/20">:</span>
                                                                                            <input type="number" name="away_score" value="{{ $match->away_score }}" class="w-16 h-8 text-center bg-white/10 border-white/10 rounded text-sm text-white focus:border-pl-green focus:ring-pl-green" placeholder="-">
                                                                                        </div>
                                                                                        
                                                                                        <span class="text-left text-sm font-medium text-white w-32 truncate">{{ $match->away_team }}</span>
                                                                                        <a href="{{ route('admin.matches.edit', $match) }}" class="text-gray-400 hover:text-pl-green transition" title="Edit Match Details">
                                                                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                                                        </a>
                                                                                    </div>

                                                                                    <!-- Actions -->
                                                                                    <div class="flex items-center gap-3">
                                                                                        <label class="flex items-center gap-1 cursor-pointer" title="Mark as Final Result (Calculates Points)">
                                                                                            <input type="checkbox" x-model="isFinal" class="rounded border-white/20 bg-white/10 text-pl-green focus:ring-pl-green w-4 h-4">
                                                                                            <span class="text-[10px] uppercase font-bold text-white/50" :class="{ 'text-pl-green': isFinal }">Final</span>
                                                                                        </label>
                                                                                        <button type="submit" class="p-1.5 text-xs font-bold text-pl-purple bg-pl-green rounded hover:bg-white transition" title="Save Score">Save</button>
                                                                                        
                                                                                         <button type="button" 
                                                                                            @click="$dispatch('open-sidebet-modal', { gameweekId: {{ $gw->id }}, selectedMatchId: {{ $match->id }}, matches: {{ $gw->matches->map(fn($m) => ['id' => $m->id, 'home_team' => $m->home_team, 'away_team' => $m->away_team]) }}, users: {{ $gw->tournament ? $gw->tournament->users->map(fn($u) => ['id' => $u->id, 'name' => $u->name]) : '[]' }} })"
                                                                                            class="p-1.5 text-xs font-bold text-white bg-blue-600 rounded hover:bg-blue-500 transition" title="Add Sidebet">Sidebet</button>
                                                                                    </div>
                                                                                </form>
                                                                            </li>
                                                                        @endforeach
                                                                     </ul>
                                                                 @else
                                                                    <div class="p-4 text-center text-xs text-white/30 italic">No matches scheduled yet.</div>
                                                                 @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            @endforeach
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="mt-8">
                        {{ $activeTournaments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
    
    @include('admin.gameweeks.partials.sidebet-modal')
</x-app-layout>