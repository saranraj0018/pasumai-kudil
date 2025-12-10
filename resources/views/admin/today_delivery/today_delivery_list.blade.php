<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        <div class="overflow-x-auto bg-white rounded-xl shadow-md" id="deliveryTableWrapper">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-2 py-2">S.No</th>
                        <th class="px-2 py-2">Delivery Partner</th>
                        <th class="px-2 py-2">Scheduled</th>
                        <th class="px-2 py-2">Quantity</th>
                        <th class="px-2 py-2">Delivered</th>
                        <th class="px-2 py-2">Pending</th>
                        <th class="px-2 py-2">Extra Quantity</th>
                        <th class="px-2 py-2">Damage Quantity</th>
                        <th class="px-2 py-2">Return Quantity</th>
                        <th class="px-2 py-2">Action</th>
                    </tr>
                </thead>
                <tbody id="deliveryListTableBody" class="divide-y divide-gray-200">
                    @if ($today_delivery->isNotEmpty())
                        @foreach ($today_delivery as $delivery)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-2 py-3">
                                    {{ $delivery->get_delivery_partner->name ?? 'N/A' }}
                                </td>
                                <td class="px-2 py-3">{{ $delivery->total_scheduled }}</td>
                                <td class="px-2 py-3 text-green-600 font-semibold">{{ $delivery->total_quantity }}</td>
                                <td class="px-2 py-3 text-green-600 font-semibold">{{ $delivery->total_delivered }}</td>
                                <td class="px-2 py-3 text-orange-600 font-semibold">{{ $delivery->total_pending }}</td>
                                <td class="px-2 py-3 text-green-600 font-semibold">{{  $delivery->get_delivery_track->extra_quantity ?? ''}}</td>
                                <td class="px-2 py-3 text-green-600 font-semibold">{{ $delivery->get_delivery_track->damage_quantity ?? '' }}</td>
                                <td class="px-2 py-3 text-green-600 font-semibold">{{ $delivery->get_delivery_track->returned_quantity ?? ''}}</td>
                                <td class="px-2 py-3 text-green-600 font-semibold">
                                    @can('edit_today_delivery')
                                    <button class="text-[#ab5f00] transition todayDeliveryList" data-delivery_partner_id="{{ $delivery->get_delivery_partner->id }}"
                                        data-extra_quantity="{{ $delivery->get_delivery_track->extra_quantity ?? '' }}"  data-damage_quantity="{{ $delivery->get_delivery_track->damage_quantity ?? '' }}"
                                        data-returned_quantity="{{ $delivery->get_delivery_track->returned_quantity ?? ''}}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center p-5">No Data Found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $today_delivery->links() }}
            </div>
            @include('admin.today_delivery.delivery_track')
        </div>
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/today_delivery_list.js') }}"></script>
