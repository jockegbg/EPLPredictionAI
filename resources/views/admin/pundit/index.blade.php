<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Admin - Pundit Articles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/10 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-white">

                    @if (session('success'))
                        <div class="bg-green-500/10 border border-green-500 text-green-500 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        ID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Article Title (Gameweek)</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach($gameweeks as $gw)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ $gw->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            {{ $gw->name }}
                                            <div class="text-xs text-gray-500">{{ $gw->start_date->format('Y-m-d') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            @if($gw->image_path)
                                                <span class="text-green-500">Img ✅</span>
                                            @else
                                                <span class="text-red-500">Img ❌</span>
                                            @endif

                                            @if($gw->pundit_summary)
                                                <span class="text-green-500 ml-2">Sum ✅</span>
                                            @else
                                                <span class="text-red-500 ml-2">Sum ❌</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <!-- Re-generate Image -->
                                            <form action="{{ route('admin.pundit.regenerate-image', $gw) }}" method="POST"
                                                class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-blue-400 hover:text-blue-300 underline"
                                                    onclick="return confirm('Regenerate Image? This uses OpenAI credits.')">
                                                    Img
                                                </button>
                                            </form>

                                            <!-- Re-generate Headers -->
                                            <form action="{{ route('admin.pundit.regenerate-summary', $gw) }}" method="POST"
                                                class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                    class="text-yellow-400 hover:text-yellow-300 underline"
                                                    onclick="return confirm('Regenerate Headers & Description?')">
                                                    Heads
                                                </button>
                                            </form>

                                            <!-- Re-generate Content -->
                                            <form action="{{ route('admin.pundit.regenerate-commentary', $gw) }}"
                                                method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                    class="text-purple-400 hover:text-purple-300 underline"
                                                    onclick="return confirm('Regenerate Match Content? This takes time.')">
                                                    Content
                                                </button>
                                            </form>

                                            <a href="{{ route('pundit.show', $gw) }}" target="_blank"
                                                class="text-gray-400 hover:text-white inline-block ml-2">
                                                view ↗
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
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