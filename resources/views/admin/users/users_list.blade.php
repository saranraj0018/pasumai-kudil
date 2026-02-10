<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
         <input type="text" name="search" id="searchInput" value="{{ $search ?? '' }}" placeholder="Search name or mobile number..." class="bg-white border rounded px-3 py-2 focus:ring focus:ring-[#ab5f00]">
        <div class="flex justify-between mb-4 items-center">
            <h2 class="text-xl font-bold">Users</h2>
            @can('add_user_list')
            <button @click="document.querySelector('#userCreateModal').__x.$data.open = true"
                class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                Create User
            </button>
            @endcan
        </div>
        <div id="userListTableWrapper" class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table id="users_table" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">S.No</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Mobile Number</th>
                        <th class="px-3 py-2">Wallet</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="divide-y divide-gray-200">
                    @if ($getuser->isNotEmpty())
                        @foreach ($getuser as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ $user->mobile_number }}</td>
                                <td class="px-4 py-3">{{ $user->get_wallet->balance ?? 0 }}</td>
                                <td class="px-4 py-3 flex justify-center gap-4">
                                    <!-- View -->
                                    @can('view_user_list')
                                    <a href="{{ route('user_view.users', ['id' => $user->id]) }}"
                                        class="text-green-600 hover:text-green-800 viewuser"
                                        data-id="{{ $user->id }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @endcan

                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">No Users found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="p-4">
                {{ $getuser->appends(['search' => $search])->links() }}
            </div>
        </div>
        @include('admin.users.add_user_modal')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/users.js') }}?v={{ time() }}"></script>
<script>
    const input = document.getElementById('searchInput');

    input.addEventListener('input', function() {
        let search = this.value;
        loadUsers(`{{ route('lists.users') }}?search=${encodeURIComponent(search)}`);
    });

    function loadUsers(url) {
        fetch(url)
            .then(res => res.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let newContent = doc.querySelector('#userListTableWrapper').innerHTML;
                document.getElementById('userListTableWrapper').innerHTML = newContent;

                attachPaginationEvents();
            })
            .catch(err => console.error(err));
    }

    function attachPaginationEvents() {
        document.querySelectorAll('#userListTableWrapper a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadUsers(this.href);
            });
        });

    }
    attachPaginationEvents();
</script>
