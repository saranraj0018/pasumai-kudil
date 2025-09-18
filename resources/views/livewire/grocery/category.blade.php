<!-- resources/views/livewire/category-manager.blade.php -->
<div class="p-4">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Categories</h2>
        <button wire:click="create" class="bg-[#ab5f00] text-white px-4 py-2 rounded">Create</button>
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
            <tbody class="divide-y divide-gray-200">
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
                        <button wire:click="edit({{ $cat->id }})"
                                class="text-blue-600 hover:text-blue-800 transition">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <!-- Delete -->
                        <button wire:click="delete({{ $cat->id }})"
                                class="text-red-600 hover:text-red-800 transition">
                            <i class="fa-solid fa-delete-left"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


    <!-- Popup Modal -->
    @if($showModal)
        <!-- Backdrop covers full screen including sidebar -->
        <div class="fixed inset-0 z-20 bg-opacity-50"></div>

        <!-- Modal -->
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-2xl w-[600px] shadow-xl">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    {{ $category_id ? 'Edit Category' : 'Add Category' }}
                </h2>

                <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" wire:model="name"
                                   class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select wire:model="status"
                                    class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Image</label>
                            <input type="file" wire:model="newImage"
                                   class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-green-500 focus:border-green-500">

                            <img src="{{ asset('storage/') }}" class="h-20 mt-3 rounded-lg shadow-md object-cover">
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="md:col-span-2 flex justify-end gap-3 pt-4 border-t">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


</div>
