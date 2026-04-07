<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">FAQs</h2>
            <button id="createFaqBtn" class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                Create
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Question</th>
                        <th class="px-3 py-2">Answer</th>
                        <th class="px-3 py-2">Sort Order</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="faqTableBody" class="divide-y divide-gray-200">
                    @if ($faqs->isNotEmpty())
                        @foreach ($faqs as $faq)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $faq->question }}</td>
                                <td class="px-4 py-3">{{ $faq->answer }}</td>
                                <td class="px-4 py-3">{{ $faq->sort_order }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $faq->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $faq->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 flex justify-center gap-4">
                                    <!-- Edit -->
                                    <button class="text-blue-600 hover:text-blue-800 transition editFaqBtn"
                                        data-id="{{ $faq->id }}" data-question="{{ $faq->question }}"
                                        data-faq_status="{{ $faq->status }}" data-answer="{{ $faq->answer }}" data-sort_order="{{ $faq->sort_order }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <!-- Delete -->
                                    <button class="text-red-600 hover:text-red-800 transition btnDeleteFaq"
                                        data-id="{{ $faq->id }}">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <td colspan="7" class="py-5 text-center">No data available</td>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $faqs->links() }}
        </div>
        @include('admin.faq.model')
    </div>

</x-layouts.app>
<script src="{{ asset('admin/js/faq.js') }}"></script>
