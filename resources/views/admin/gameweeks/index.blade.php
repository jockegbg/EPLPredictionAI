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
                    <div class="flex flex-col md:flex-row justify-between mb-6 gap-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-lg font-bold text-white">Gameweeks</h3>
                            <form method="GET" action="{{ route('admin.gameweeks.index') }}">
                                <select name="tournament_id" onchange="this.form.submit()"
                                    class="bg-white/10 text-white border-white/20 rounded-md shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm backdrop-blur-md">
                                    <option value="" class="bg-slate-800 text-white">All Tournaments</option>
                                    @foreach($tournaments as $tournament)
                                        <option value="{{ $tournament->id }}" class="bg-slate-800 text-white" {{ request('tournament_id') == $tournament->id ? 'selected' : '' }}>
                                            {{ $tournament->name }} {{ $tournament->is_active ? '(Active)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.import.create') }}"
                                class="bg-pl-purple hover:bg-pl-purple/80 text-white font-bold py-2 px-4 rounded-full shadow-lg border border-white/10 transition transform hover:scale-105 text-center">
                                Import from FPL
                            </a>
                            <a href="{{ route('admin.gameweeks.create') }}"
                                class="bg-pl-green hover:bg-pl-green/90 text-slate-900 font-bold py-2 px-4 rounded-full shadow-lg transition transform hover:scale-105 text-center">
                                + New Gameweek
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-pl-green/20 text-pl-green border border-pl-green/50 p-4 rounded mb-4 font-bold">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                                @foreach($gameweeks as $gw)
                                    <tbody x-data="{ expanded: false }" class="border-b border-white/5">
                                        <tr class="hover:bg-pl-purple/50 transition bg-white/5">
                                            <td class="px-6 py-4 whitespace-nowrap text-white font-medium flex items-center gap-2">
                                                <button @click="expanded = !expanded" class="text-pl-green hover:text-white transition focus:outline-none">
                                                    <svg x-show="!expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    <svg x-show="expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                </button>
                                                {{ $gw->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-white">
                                                {{ $gw->start_date->format('M d') }} - {{ $gw->end_date->format('M d') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-bold rounded-full 
                                                    {{ $gw->status === 'active' ? 'bg-pl-green text-pl-purple' :
                                                    ($gw->status === 'completed' ? 'bg-white text-slate-900' : 'bg-blue-600 text-white') }}">
                                                    {{ ucfirst($gw->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center">
                                                <a href="{{ route('admin.gameweeks.edit', $gw) }}"
                                                    class="text-pl-blue hover:text-white mr-3 font-bold transition">Edit</a>
                                                <a href="{{ route('admin.matches.create', $gw) }}"
                                                    class="text-pl-green hover:text-white font-bold transition mr-3">Add Match</a>
                                                
                                                <form id="delete-gameweek-{{ $gw->id }}" method="POST" action="{{ route('admin.gameweeks.destroy', $gw) }}" class="inline-block" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button type="button" 
                                                        onclick="if(confirm('Are you sure you want to delete this gameweek? All matches and predictions within it will be deleted.')) { document.getElementById('delete-gameweek-{{ $gw->id }}').submit(); }"
                                                        class="text-pl-pink hover:text-white font-bold transition">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        {{-- Matches Sub-table --}}
                                        @if($gw->matches->count() > 0)
                                            <tr x-show="expanded" x-transition.opacity.duration.300ms>
                                                <td colspan="4" class="px-6 py-4 bg-[#2f0034]/50 border-b border-white/5">
                                                    <div class="text-xs font-bold text-white/50 uppercase mb-3 flex items-center gap-2">
                                                        <div class="h-px bg-white/20 flex-1"></div>
                                                        <span>Matches in {{ $gw->name }}</span>
                                                        <div class="h-px bg-white/20 flex-1"></div>
                                                    </div>
                                                    
                                                    <ul class="space-y-2">
                                                        @foreach($gw->matches as $match)
                                                            <li class="group text-sm py-2 px-4 rounded-lg bg-white/5 border border-white/5 hover:border-white/20 transition"
                                                                x-data="{ localTime: new Date('{{ $match->start_time->toIso8601String() }}').toLocaleString() }">
                                                                
                                                                <form action="{{ route('admin.matches.update', $match) }}" method="POST"
                                                                    class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                                                    @csrf
                                                                    @method('PUT')

                                                                    <input type="hidden" name="home_team" value="{{ $match->home_team }}">
                                                                    <input type="hidden" name="away_team" value="{{ $match->away_team }}">
                                                                    <input type="hidden" name="start_time" value="{{ $match->start_time }}">
                                                                    <input type="hidden" name="status" value="completed">

                                                                    {{-- Teams & Score --}}
                                                                    <div class="flex items-center gap-4 w-full sm:w-auto justify-center">
                                                                        <div class="flex items-center gap-2 w-32 justify-end">
                                                                            <span class="font-bold text-white text-right truncate">{{ $match->home_team }}</span>
                                                                        </div>

                                                                        <div class="flex items-center gap-2">
                                                                            <input type="number" name="home_score"
                                                                                value="{{ $match->home_score }}"
                                                                                class="w-12 h-9 text-center bg-white/10 border-white/20 text-white rounded focus:ring-pl-green focus:border-pl-green font-bold"
                                                                                placeholder="-">
                                                                            <span class="text-white/30 font-bold">-</span>
                                                                            <input type="number" name="away_score"
                                                                                value="{{ $match->away_score }}"
                                                                                class="w-12 h-9 text-center bg-white/10 border-white/20 text-white rounded focus:ring-pl-green focus:border-pl-green font-bold"
                                                                                placeholder="-">
                                                                        </div>

                                                                        <div class="flex items-center gap-2 w-32 justify-start">
                                                                            <span class="font-bold text-white text-left truncate">{{ $match->away_team }}</span>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Actions & Info --}}
                                                                    <div class="flex items-center gap-4 text-xs text-white/50">
                                                                        <span x-text="localTime" class="hidden md:inline">{{ $match->start_time->format('M d H:i') }} UTC</span>
                                                                        <button type="submit"
                                                                            class="bg-pl-green hover:bg-white text-pl-purple font-bold py-1.5 px-3 rounded transition shadow-lg hover:shadow-pl-green/50">
                                                                            Save Score
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @endforeach
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $gameweeks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>