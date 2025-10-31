<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        {{-- <input
        type="text" id="searchInput"
        placeholder="Search Delivery Partner..."
        class="border p-2 rounded w-40 mb-4 shadow-md"> --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow-md" id="deliveryTableWrapper">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">S.No</th>
                        <th class="px-3 py-2">Delivery Date</th>
                        <th class="px-3 py-2">User Name</th>
                        <th class="px-3 py-2">Delivery Partner</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="deliveryListTableBody" class="divide-y divide-gray-200">
                    @if ($daily_delivery->isNotEmpty())
                        @foreach ($daily_delivery as $list)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $list->delivery_date ?? '' }}</td>
                                <td class="px-4 py-3">{{ $list->get_user->name ?? '' }}</td>
                                <td class="px-4 py-3">{{ $list->get_delivery_partner->name ?? '' }}</td>
                                <td class="px-4 py-3">{{ $list->amount ?? '' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $isDelivered = $list->delivery_status === 'delivered';
                                    @endphp


                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full
        {{ $isDelivered ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $isDelivered ? 'Delivered' : 'Pending' }}
                                    </span>
                                </td>
                                @if ($list->delivery_status == 'pending')
                                    <td class="px-4 py-3 flex justify-center gap-4">
                                        <!-- Edit -->
                                        <button class="text-blue-600 hover:text-blue-800 transition editDeliveryList"
                                            data-id="{{ $list->id }}" data-user_id="{{ $list->user_id ?? '' }}"
                                            data-status="{{ $list->delivery_status ?? '' }}"
                                            data-image="{{ $list->image ? asset('storage/' . $list->image) : '' }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center p-5">No Data Found</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="p-4">
                {{ $daily_delivery->links() }}
            </div>
        </div>
        @include('admin.delivery_list.edit_delivery_status')
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/delivery_list.js') }}"></script>
