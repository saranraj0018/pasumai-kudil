<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Milk Subscriptions</h2>
            <div class="flex gap-2">
                @can('add_subscription')
                    <button id="configTimeBtn" class="bg-[#ab5e00] text-white px-4 py-2 rounded flex items-center gap-2">
                        <i class="fa fa-clock"></i> Config Time
                    </button>
                @endcan
                @can('add_subscription')
                    <button id="createSubscriptionBtn"
                        class="bg-[#ab5f00] text-white px-4 py-2 rounded flex items-center gap-2">
                        <i class="fa fa-plus"></i> Create
                    </button>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Plan Name</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Duration Days</th>
                        <th class="px-3 py-2">Quantity</th>
                        <th class="px-3 py-2">Pack</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptionTableBody" class="divide-y divide-gray-200">
                    @foreach ($subscriptions as $sub)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $sub->plan_name ?? '' }}</td>
                            <td class="px-4 py-3">₹{{ number_format($sub->plan_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ $sub->plan_type ?? '' }}</td>
                            <td class="px-4 py-3">{{ $sub->plan_duration ?? '' }}</td>
                            <td class="px-4 py-3">{{ $sub->quantity ?? '' }}</td>
                            <td class="px-4 py-3">{{ $sub->pack ?? '' }}</td>
                            <td class="px-4 py-3 flex justify-center gap-4">
                                @can('edit_subscription')
                                    <button class="text-blue-600 hover:text-blue-800 transition editSubscriptionBtn"
                                        data-id="{{ $sub->id ?? '' }}" data-amount="{{ $sub->plan_amount ?? '' }}"
                                        data-pack="{{ $sub->plan_pack ?? '' }}" data-type="{{ $sub->plan_type ?? '' }}"
                                        data-duration="{{ $sub->plan_duration ?? '' }}"
                                        data-details="{{ implode(', ', $sub->plan_details ?? '') }}"
                                        data-quantity="{{ $sub->quantity ?? '' }}"
                                        data-pack_details="{{ $sub->pack ?? '' }}"
                                        data-delivery_days="{{ $sub->delivery_days ?? '' }}"
                                        data-plan_name="{{ $sub->plan_name ?? '' }}"
                                        data-is_show_mobile="{{ $sub->is_show_mobile ?? '' }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                @endcan
                                @if (isset($sub->get_user) && !empty($sub->get_user))
                                    @can('delete_subscription')
                                        <button class="text-red-600 hover:text-red-800 transition btnDeleteSubscription"
                                            data-id="{{ $sub->id }}">
                                            <i class="fa-solid fa-delete-left"></i>
                                        </button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $subscriptions->links() }}
        </div>
        @include('admin.subscription.modal')
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/subscription.js') }}"></script>
