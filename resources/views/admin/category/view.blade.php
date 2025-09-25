
<x-layouts.app>
    <div   class="p-4" x-data="{ open: false }">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Categories</h2>
            <button @click="document.querySelector('#categoryModal').__x.$data.open = true" class="bg-[#ab5f00] text-white px-4 py-2 rounded">Create</button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Image</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">User</th>
                    <th class="px-3 py-2">Created At</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
                </thead>
                <tbody id="categoryTableBody" class="divide-y divide-gray-200">
                @foreach($categories as $cat)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $cat->id }}</td>
                        <td class="px-4 py-3">{{ $cat->name }}</td>
                        <td class="px-4 py-3">
                            @if($cat->image)
                                <img src="{{ asset('storage/'.$cat->image) }}"
                                     class="h-10 w-10 object-cover rounded-lg shadow-sm border" />
                            @else
                                <span class="text-gray-400 italic">No Image</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $cat->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $cat->status ? 'Active' : 'Inactive' }}
                        </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ $cat->admin?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $cat->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-4">
                            <!-- Edit -->
                            <button class="text-blue-600 hover:text-blue-800 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Delete -->
                            <button class="text-red-600 hover:text-red-800 transition">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.category.model')
    </div>

</x-layouts.app>
<script src="{{ asset('admin/js/category.js') }}"></script>

