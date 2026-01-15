<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Search -->
            <div class="mb-6">
                <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2">
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                        class="w-full md:w-1/3" />
                    <x-primary-button>Search</x-primary-button>
                </form>
            </div>

            <div class="bg-pl-purple-dark overflow-hidden shadow-sm sm:rounded-lg border border-pl-pink/20">
                <div class="p-6 text-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-pl-pink border-b border-pl-pink/20">
                                <th class="p-3">ID</th>
                                <th class="p-3">Name</th>
                                <th class="p-3">Email</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Joined</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b border-pl-pink/10 hover:bg-pl-purple/50">
                                    <td class="p-3 text-gray-400">#{{ $user->id }}</td>
                                    <td class="p-3 font-bold">{{ $user->name }}</td>
                                    <td class="p-3 text-gray-300">{{ $user->email }}</td>
                                    <td class="p-3">
                                        @if($user->is_admin)
                                            <span
                                                class="bg-pl-green text-black px-2 py-0.5 rounded text-xs font-bold">ADMIN</span>
                                        @else
                                            <span class="text-gray-500 text-xs">User</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-sm text-gray-400">{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="text-pl-blue hover:text-white underline">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>