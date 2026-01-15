<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Tournaments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.tournaments.create') }}"
                    class="bg-white hover:bg-gray-100 text-zinc-900 font-bold py-2 px-4 rounded shadow text-xs uppercase tracking-widest">
                    Create Tournament
                </a>
            </div>

            <div class="bg-white/10 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-lg border border-white/20">
                <div class="p-6 text-white">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs leading-4 font-medium text-gray-300 uppercase tracking-wider">
                                    Name</th>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs leading-4 font-medium text-gray-300 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs leading-4 font-medium text-gray-300 uppercase tracking-wider">
                                    Gameweeks</th>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs leading-4 font-medium text-gray-300 uppercase tracking-wider">
                                    Participants</th>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs leading-4 font-medium text-gray-300 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($tournaments as $tournament)
                                <tr>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ $tournament->name }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        @if($tournament->is_active)
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ $tournament->gameweeks->count() }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ $tournament->users->count() }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 font-medium">
                                        <a href="{{ route('admin.tournaments.edit', $tournament) }}"
                                            class="text-indigo-400 hover:text-indigo-300 mr-4">Edit</a>
                                        <form action="{{ route('admin.tournaments.destroy', $tournament) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300"
                                                onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{-- {{ $tournaments->links() }} --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>