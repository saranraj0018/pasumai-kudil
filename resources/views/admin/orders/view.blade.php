<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Orders</h2>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Order ID</th>
                        <th class="px-3 py-2">User</th>
                        <th class="px-3 py-2">Phone</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2 text-center">Order Date</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody" class="divide-y divide-gray-200">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">{{ $order->id }}</td>
                            <td class="px-4 py-3 font-mono">{{ $order->order_id }}</td>
                            <td class="px-4 py-3">{{ $order->user->name ?? 'Guest' }}</td>
                            <td class="px-4 py-3">{{ $order->phone }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">₹{{ number_format($order->gross_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ optional($order->created_at)->format('d M Y h:i A') }}</td>
                            <td class="px-4 py-3 text-center">
                                @can('edit_orders')
                                <button class="text-blue-500 hover:text-blue-700 viewOrderBtn"
                                    data-order='@json($order)'>
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $orders->links() }}
        </div>
          @include('admin.orders.modal')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/order.js') }}?v={{ time() }}"></script>
