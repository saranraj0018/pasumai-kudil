<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        {{-- <input
        type="text" id="searchInput"
        placeholder="Search Delivery Partner..."
        class="border p-2 rounded w-40 mb-4 shadow-md"> --}}

        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Delivery Partner</h2>
            <button @click="document.querySelector('#deliveryPartnerCreateModal').__x.$data.open = true"
                class="bg-[#ab5f00] text-white px-4 py-2 rounded add_delivery_partner">
                Create Delivery Partner
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md" id="deliveryTableWrapper">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">S.No</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Mobile Number</th>
                    <th class="px-3 py-2">Hub</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="deliveryPartnerTableBody" class="divide-y divide-gray-200">
                    @if ($delivery_partner->isNotEmpty())
                    @foreach ($delivery_partner as $partner)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $partner->name ?? ''}}</td>
                            <td class="px-4 py-3">{{ $partner->mobile_number ?? '' }}</td>
                            <td class="px-4 py-3">{{ $partner->get_hub->name ?? ''}}</td>
                           <td class="px-4 py-3 flex justify-center gap-4">
                            <!-- Edit -->
                            <button
                                class="text-blue-600 hover:text-blue-800 transition editDeliveryPartner"
                                data-id="{{ $partner->id }}"
                                data-name="{{ $partner->name ?? ''}}"
                                data-mobile_number="{{ $partner->mobile_number ?? '' }}"
                                data-hub_id="{{ $partner->hub_id ?? ''}}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Delete -->
                            @if ($partner->get_daily_deliveries->isEmpty())
                            <button class="text-red-600 hover:text-red-800 deleteDeliveryPartner" data-id="{{ $partner->id }}">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
                            @endif
                        </td>
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
                {{ $delivery_partner->appends(['search' => $search])->links() }}
            </div>
        </div>
        @include('admin.delivery_partner.create_delivery_partner')
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/delivery_partner.js') }}"></script>
