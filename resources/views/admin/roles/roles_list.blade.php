<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Roles</h2>
            @can('add_role')
            <button id="createRole" class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                Create
            </button>
            @endcan
        </div>
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
                </thead>
                <tbody id="roleTableBody" class="divide-y divide-gray-200">
                @foreach($roles as $role)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $role->id }}</td>
                        <td class="px-4 py-3">{{ $role->name ?? '' }}</td>
                        <td class="px-4 py-3 flex justify-center gap-4">
                            @can('edit_category')
                            <!-- Edit -->
                            <button class="text-blue-600 hover:text-blue-800 transition editRoleBtn" data-id="{{ $role->id }}" data-name="{{ $role->name }}">
                             <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            @endcan
                            <!-- Delete -->
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
         <div class="p-4">
            {{ $roles->links() }}
        </div>
        @include('admin.roles.add_role_model')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/role.js') }}?v={{ time() }}"></script>

