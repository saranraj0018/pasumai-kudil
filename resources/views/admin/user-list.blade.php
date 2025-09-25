@extends('layouts.app')
@section('content')
<div class="p-4">
    <div class="flex justify-between mb-4 items-center">
        <h2 class="text-xl font-bold">Users</h2>

        <input
            type="text"
            name="search"
            id="searchInput"
            value="{{ $search ?? '' }}"
            placeholder="Search name or mobile number..."
            class="border rounded px-3 py-2 focus:ring-2 focus:ring-green-500"
        >
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow-md" id="userTableWrapper">
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
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $user->id }}</td>
                    <td class="px-4 py-3">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->mobile_number }}</td>
                    <td class="px-4 py-3">{{ $user->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $users->appends(['search' => $search])->links() }}
        </div>
    </div>
</div>

{{-- Ajax Script --}}
<script>
    const input = document.getElementById('searchInput');

    input.addEventListener('input', function() {
        let search = this.value;
        loadUsers(`{{ route('view.users') }}?search=${encodeURIComponent(search)}`);
    });

    function loadUsers(url) {
        fetch(url)
            .then(res => res.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let newContent = doc.querySelector('#userTableWrapper').innerHTML;
                document.getElementById('userTableWrapper').innerHTML = newContent;

                attachPaginationEvents();
            })
            .catch(err => console.error(err));
    }

    function attachPaginationEvents() {
        document.querySelectorAll('#userTableWrapper a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadUsers(this.href);
            });
        });

    }
    attachPaginationEvents();
</script>
@endsection
