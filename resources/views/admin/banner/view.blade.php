<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Banners</h2>
            <button id="createBannerBtn" class="bg-[#ab5f00] text-white px-4 py-2 rounded">Create</button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Image</th>
                        <th class="px-3 py-2">Created At</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="bannerTableBody" class="divide-y divide-gray-200">
                    @foreach ($banners as $banner)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">{{ $banner->id }}</td>
                            <td class="px-4 py-3">
                             <span
                             class="px-3 py-1 text-xs font-semibold rounded-full
                       @if ($banner->type == 'GroceryMain') bg-green-100 text-green-700
                       @elseif($banner->type == 'GrocerySub')
                      bg-yellow-100 text-yellow-700
                      @elseif($banner->type == 'MilkMain')
                      bg-blue-100 text-blue-700
                      @elseif($banner->type == 'MilkSub')
                      bg-purple-100 text-purple-700
                      @else
                      bg-gray-100 text-gray-700 @endif">
                        {{ $banner->type }}
                        </span>
                    </td>
                         <td class="px-4 py-3">
                                @if ($banner->image_url)
                                    <img src="{{ asset('storage/' . $banner->image_url) }}"
                                        class="h-10 w-10 object-cover rounded-lg" />
                                @else
                                    <span class="text-gray-400 italic">No Image</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $banner->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 flex justify-center gap-4">
                                <button class="text-blue-600 hover:text-blue-800 transition editBannerBtn"
                                    data-id="{{ $banner->id }}" data-type="{{ $banner->type }}"
                                    data-priority="{{ $banner->priority }}"
                                    data-image="{{ $banner->image_url ? asset('storage/' . $banner->image_url) : '' }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800 transition btnDeleteBanner"
                                    data-id="{{ $banner->id }}">
                                    <i class="fa-solid fa-delete-left"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $banners->links() }}
        </div>

        @include('admin.banner.modal')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/banner.js') }}"></script>
