<x-layouts.app>
<div class="p-4">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Milk Subscriptions</h2>
        <button id="createSubscriptionBtn" class="bg-[#ab5f00] text-white px-4 py-2 rounded">
            Create
        </button>
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow-md">
        <table class="w-full text-sm text-left text-gray-700 border-collapse">
            <thead>
                <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Plan</th>
                    <th class="px-3 py-2">Amount</th>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">Duration</th>
                    <th class="px-3 py-2">Quantity</th>
                    <th class="px-3 py-2">Pack</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
            </thead>
          <tbody id="subscriptionTableBody" class="divide-y divide-gray-200">
    @foreach ($subscriptions as $sub)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 font-medium text-gray-900">{{ $sub->id }}</td>
            <td class="px-4 py-3">{{ $sub->plan_pack }}</td>
            <td class="px-4 py-3">₹{{ number_format($sub->plan_amount, 2) }}</td>
            <td class="px-4 py-3">{{ $sub->plan_type }}</td>
            <td class="px-4 py-3">{{ $sub->plan_duration }}</td>
            <td class="px-4 py-3">{{ $sub->quantity }}</td>
            <td class="px-4 py-3">{{ $sub->pack }}</td>
            <td class="px-4 py-3 flex justify-center gap-4">
                <button class="text-blue-600 hover:text-blue-800 transition editSubscriptionBtn"
                    data-id="{{ $sub->id }}"
                    data-amount="{{ $sub->plan_amount }}"
                    data-pack="{{ $sub->plan_pack }}"
                    data-type="{{ $sub->plan_type }}"
                    data-duration="{{ $sub->plan_duration }}"
                    data-details="{{ $sub->plan_details }}"
                   data-quantity="{{ $sub->quantity }}"
                   data-pack_details="{{ $sub->pack }}"
                  data-delivery_days="{{ $sub->delivery_days }}">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                            <button class="text-red-600 hover:text-red-800 transition btnDeleteSubscription"
                                data-id="{{ $sub->id }}">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
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
