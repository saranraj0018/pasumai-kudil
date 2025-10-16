<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Hub List</h2>
            <button id="create_hub" class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                Create Hub
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Created At</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
                </thead>
                <tbody id="categoryTableBody" class="divide-y divide-gray-200">
                @foreach($hub_list as $list)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $list->id }}</td>
                        <td class="px-4 py-3">{{ $list->name }}</td>
                        <td class="px-4 py-3">{{ $list->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $list->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $list->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ showDate($list->created_at) }}</td>
                        <td class="px-4 py-3 flex justify-center gap-4">
                            <!-- Edit -->
                            <button
                                class="text-blue-600 hover:text-blue-800 transition editHubBtn"
                                data-id="{{ $list->id }}"
                                data-name="{{ $list->name }}"
                                data-email="{{ $list->email }}"
                                data-status="{{ $list->status }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Delete -->
                            <button class="text-red-600 hover:text-red-800 transition deleteHubBtn"
                                    data-id="{{ $list->id }}">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $hub_list->links() }}
        </div>
            @include('admin.hub.model')
    </div>
</x-layouts.app>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places"></script>
<script src="{{ asset('admin/js/hub.js') }}"></script>
