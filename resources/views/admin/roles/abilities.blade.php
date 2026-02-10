<x-layouts.app>
    <div class="container mx-auto p-6 bg-white">

        <h2 class="text-2xl font-semibold mb-6 text-center">Role Permissions</h2>
        @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    showToast("{{ session('success') }}", "success");
                });
            </script>
        @endif
        @if (session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    showToast("{{ session('error') }}", "error");
                });
            </script>
        @endif


        {{-- Role Select --}}
        <form method="GET" id="roleSelectForm" class="mb-6 w-1/2 mx-auto">
            <label for="role_id" class="block text-gray-700 font-medium mb-2">Select Role</label>
            <select name="role_id" id="role_id"
                class="border border-gray-300 rounded w-full p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]"
                onchange="document.getElementById('roleSelectForm').submit()">
                <option selected disabled value="">Please Select Role</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->id }}" {{ $role && $role->id == $r->id ? 'selected' : '' }}>
                        {{ $r->name }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Global Select All --}}
        <div class="flex justify-end mb-3">
            <label class="flex items-center space-x-2 cursor-pointer">
                <span class="text-gray-700 font-medium">Select All Permissions</span>
                <input type="checkbox" id="globalSelectAll" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
            </label>
        </div>

        {{-- Abilities Form --}}
        <form method="POST" action="{{ route('roles_and_permission_save') }}">
            @csrf
            <input type="hidden" name="role_id" value="{{ $role->id ?? '' }}">

            @foreach ($abilities->where('main_menu', 'y') as $menu)
                @php
                    $subMenus = $abilities->where('menu_id', $menu->id)->where('main_menu', 'n');
                @endphp

                {{-- Main Menu Section --}}
                <div class="mb-6 border rounded bg-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            {{-- Main Menu as Permission --}}
                            <input type="checkbox"
                                class="main-menu-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded"
                                name="abilities[]" value="{{ $menu->id }}" data-menu="{{ $menu->id }}"
                                {{ in_array($menu->id, $roleAbilities) ? 'checked' : '' }}>
                            <span class="font-bold text-gray-800 text-lg">{{ $menu->title }}</span>
                        </label>

                        {{-- Main Menu Select All Submenus --}}
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <span class="text-sm text-gray-700">Select All Submenu Permissions</span>
                            <input type="checkbox"
                                class="select-all-submenu h-4 w-4 text-blue-600 border-gray-300 rounded"
                                data-menu="{{ $menu->id }}">
                        </label>
                    </div>

                    {{-- Submenu Table --}}
                    <div class="overflow-x-auto mt-2">
                        <table class="w-full table-auto border-collapse border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 p-2 text-left">Submenu</th>
                                    <th class="border border-gray-300 p-2 text-center">Add</th>
                                    <th class="border border-gray-300 p-2 text-center">View</th>
                                    <th class="border border-gray-300 p-2 text-center">Edit</th>
                                    <th class="border border-gray-300 p-2 text-center">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subMenus as $submenu)
                                    @php
                                        $add = $abilities
                                            ->where('menu_id', $submenu->id)
                                            ->where('ability', 'add_' . $submenu->ability)
                                            ->first();
                                        $view = $abilities
                                            ->where('menu_id', $submenu->id)
                                            ->where('ability', 'view_' . $submenu->ability)
                                            ->first();
                                        $edit = $abilities
                                            ->where('menu_id', $submenu->id)
                                            ->where('ability', 'edit_' . $submenu->ability)
                                            ->first();
                                        $delete = $abilities
                                            ->where('menu_id', $submenu->id)
                                            ->where('ability', 'delete_' . $submenu->ability)
                                            ->first();
                                    @endphp
                                    <tr class="bg-white">
                                        <td class="border p-2">{{ $submenu->title }}</td>
                                        <td class="border p-2 text-center">
                                            @if ($add)
                                                <input type="checkbox"
                                                    class="ability-checkbox child-{{ $menu->id }}"
                                                    name="abilities[]" value="{{ $add->id }}"
                                                    {{ in_array($add->id, $roleAbilities) ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                        <td class="border p-2 text-center">
                                            @if ($view)
                                                <input type="checkbox"
                                                    class="ability-checkbox child-{{ $menu->id }}"
                                                    name="abilities[]" value="{{ $view->id }}"
                                                    {{ in_array($view->id, $roleAbilities) ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                        <td class="border p-2 text-center">
                                            @if ($edit)
                                                <input type="checkbox"
                                                    class="ability-checkbox child-{{ $menu->id }}"
                                                    name="abilities[]" value="{{ $edit->id }}"
                                                    {{ in_array($edit->id, $roleAbilities) ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                        <td class="border p-2 text-center">
                                            @if ($delete)
                                                <input type="checkbox"
                                                    class="ability-checkbox child-{{ $menu->id }}"
                                                    name="abilities[]" value="{{ $delete->id }}"
                                                    {{ in_array($delete->id, $roleAbilities) ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            {{-- Submit Button Center --}}
            @can('add_roles_permissions')
                <div class="flex justify-center mt-6">
                    <button type="submit" id="save_ability"
                        class="bg-[#ab5f00] hover:bg-[#ab5e00fd] text-white px-6 py-2 rounded font-medium">
                        Save
                    </button>
                </div>
            @endcan
        </form>
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/role.js') }}?v={{ time() }}"></script>
