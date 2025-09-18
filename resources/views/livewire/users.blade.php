<?php

use App\Models\User;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getUsersProperty()
    {
       $search = $this->search;
         return User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }
};

?>

<div class="p-6 bg-gray-900 min-h-screen text-white">
    <h2 class="text-2xl font-bold mb-6 text-center">👥 User List</h2>

    <!-- Search & Per Page -->
    {{-- <div class="flex justify-between items-center mb-4">
        <input
            type="text"
            wire:model.debounce.300ms="search"
            placeholder="🔍 Search by name, email or mobile..."
            class="px-4 py-2 w-1/3 rounded-md bg-gray-800 border border-gray-600 text-white focus:ring-2 focus:ring-indigo-500"
        >
    </div> --}}

    <!-- Table -->
    <div class="overflow-x-auto shadow-lg rounded-lg">
        <table class="min-w-full bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-left">
                    <th class="px-6 py-3 text-sm font-semibold">ID</th>
                    <th class="px-6 py-3 text-sm font-semibold">Name</th>
                    <th class="px-6 py-3 text-sm font-semibold">Mobile Number</th>
                    <th class="px-6 py-3 text-sm font-semibold">Created Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($this->users as $user)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-3">{{ $user->id }}</td>
                        <td class="px-6 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-3 text-gray-300">{{ $user->mobile_number }}</td>
                        <td class="px-6 py-3 text-gray-400">{{ $user->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-center text-gray-400">
                            🚫 No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $this->users->links('pagination::tailwind') }}
    </div>
</div>
