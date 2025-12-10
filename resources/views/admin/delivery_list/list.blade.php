<x-layouts.app>
    <div class="p-4">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('lists.delivery_list') }}" class="bg-white p-5 rounded-lg shadow flex flex-wrap gap-4 items-end">
            <!-- City Filter -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <select name="city" id="city" class="block w-52 rounded-lg border border-gray-300 p-2 outline-none">
                    <option value="">All Cities</option>
                    @foreach ($hub_list as $city)
                        <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Delivery Date Filter -->
            <div>
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                <input type="date" name="delivery_date" id="delivery_date" class="block w-52 rounded-lg border border-gray-300 p-2 outline-none" value="{{ request('delivery_date') }}">
            </div>

            <!-- Month Filter -->
            <div>
                <label for="delivery_month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <input type="month" name="delivery_month" id="delivery_month" class="block w-52 rounded-lg border border-gray-300 p-2 outline-none" value="{{ request('delivery_month') }}">
            </div>

            <!-- Username Filter -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" id="username" placeholder="Search by username" class="block w-52 rounded-lg border border-gray-300 p-2 outline-none" value="{{ request('username') }}">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="block w-52 rounded-lg border border-gray-300 p-2 outline-none">
                    <option value="">All</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <!-- Delivery Boy Filter -->
            <div>
                <label for="delivery_boy" class="block text-sm font-medium text-gray-700 mb-1">Delivery Boy</label>
                <select name="delivery_boy" id="delivery_boy" class="block w-52 rounded-lg border border-gray-300 p-2 outline-none">
                    <option value="">All</option>
                    @foreach ($delivery_boy as $boy)
                        <option value="{{ $boy->name }}" {{ request('delivery_boy') == $boy->name ? 'selected' : '' }}>
                            {{ $boy->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter & Reset Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#ab5f00] text-white rounded-lg hover:bg-[#ab7d00]">Filter</button>
                <a href="{{ route('lists.delivery_list') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset</a>
            </div>
        </form>

        <!-- Delivery Table -->
        <div class="mt-5 overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">S.No</th>
                        <th class="px-3 py-2">Delivery Date</th>
                        <th class="px-3 py-2">User Name</th>
                        <th class="px-3 py-2">Address</th>
                        <th class="px-3 py-2">Delivery Partner</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($daily_delivery as $list)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $list->delivery_date ?? '' }}</td>
                            <td class="px-4 py-3">{{ $list->get_user->name ?? '' }}</td>
                            <td class="px-4 py-3">{{ $list->get_user->address ?? '' }}</td>
                            <td class="px-4 py-3">{{ $list->get_delivery_partner->name ?? '' }}</td>
                            <td class="px-4 py-3">{{ $list->amount ?? '' }}</td>
                            <td class="px-4 py-3">
                                @php $isDelivered = $list->delivery_status === 'delivered'; @endphp
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $isDelivered ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $isDelivered ? 'Delivered' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @can('edit_delivery_list')
                                @if ($list->delivery_status == 'pending')
                                    <button class="text-blue-600 hover:text-blue-800 transition editDeliveryList"
                                        data-id="{{ $list->id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                @else
                                    -
                                @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center p-5">No Data Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="p-4">
                {{ $daily_delivery->links() }}
            </div>
        </div>
        <!-- Edit Delivery Status Modal -->
        @include('admin.delivery_list.edit_delivery_status', ['delivery_boy' => $delivery_boy])
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/delivery_list.js') }}"></script>
