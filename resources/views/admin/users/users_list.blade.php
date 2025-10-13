<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
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
                                    <a href="{{ route('user_view.users', ['id' => $user->id]) }}"
                                        class="text-green-600 hover:text-green-800 viewuser"
                                        data-id="{{ $user->id }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

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
        </div>
        <div class="p-4">
            {{ $getuser->links() }}
        </div>
    </div>
</x-layouts.app>
