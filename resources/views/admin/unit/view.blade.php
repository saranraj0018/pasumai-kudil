<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Units</h2>
            <button id="createUnitBtn" class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                Create
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Short Name</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="unitTableBody" class="divide-y divide-gray-200">
                    @if ($units->isNotEmpty())
                    @foreach ($units as $unit)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $unit->name }}</td>
                            <td class="px-4 py-3">{{ $unit->short_name }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $unit->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $unit->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 flex justify-center gap-4">
                                <!-- Edit -->
                                <button class="text-blue-600 hover:text-blue-800 transition editUnitBtn"
                                    data-id="{{ $unit->id }}" data-name="{{ $unit->name }}"
                                    data-status="{{ $unit->status }}" data-short_name="{{ $unit->short_name }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <!-- Delete -->
                                <button class="text-red-600 hover:text-red-800 transition btnDeleteUnit"
                                    data-id="{{ $unit->id }}">
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
            {{ $units->links() }}
        </div>
        @include('admin.unit.model')
    </div>

</x-layouts.app>
<script src="{{ asset('admin/js/unit.js') }}"></script>
