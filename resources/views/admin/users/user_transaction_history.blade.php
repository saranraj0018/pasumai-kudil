<x-layouts.app>
    <a href="{{ route('user_view.users',['id' => request()->id])}}"><i class="fa-solid fa-arrow-left">‌</i></a>
    <div class="p-4">
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">S.No</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">amount</th>
                    <th class="px-3 py-2">Created Date</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="divide-y divide-gray-200">
                    @if ($transactions->isNotEmpty())
                    @foreach ($transactions as $transac)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $transac->get_user->name }}</td>
                            <td class="px-4 py-3">{{ $transac->type }}</td>
                            <td class="px-4 py-3">{{ $transac->amount }}</td>
                            <td class="px-4 py-3">
                                @if (!empty($transac->created_at))
                                {{ $transac->created_at->format('d M Y, h:i A') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="5" class="py-3 text-center">No Transactions found</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="p-4">
        {{ $transactions->links() }}
        </div>
    </div>
</x-layouts.app>

