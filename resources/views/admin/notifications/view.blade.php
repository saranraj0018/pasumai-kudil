<x-layouts.app>
  <div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-semibold mb-4">Notifications</h2>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Title</th>
                    <th class="px-4 py-3 text-left">Message</th>
                    <th class="px-4 py-3 text-left">Time</th>
                    {{-- <th class="px-4 py-3 text-center">Status</th> --}}
                </tr>
            </thead>

            <tbody>
                @forelse ($notifications as $note)
                    <tr class="{{ $note->status == 0 ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3">{{ $note->title ?? '' }}</td>
                        <td class="px-4 py-3">{{ $note->description ?? '' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $note->created_at->diffForHumans() ?? ''}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            No notifications found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    </div>
</x-layouts.app>


