<div class="p-4">
    <div class="flex justify-between mb-4 items-center">
        <h2 class="text-xl font-bold">Users</h2>

        <input
            type="text"
            wire:model.live.debounce.500ms="search"
            placeholder="Search name or mobile number..."
            class="border rounded px-3 py-2 focus:ring-2 focus:ring-green-500"
        >
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow-md">
        <table class="w-full text-sm text-left text-gray-700 border-collapse">
            <thead>
                <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Mobile Number</th>
                    <th class="px-3 py-2">Created Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->id }}</td>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->mobile_number }}</td>
                        <td class="px-4 py-3">{{ $user->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
