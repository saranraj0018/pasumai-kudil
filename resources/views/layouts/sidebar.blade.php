<aside class="bg-gradient-to-b from-[#ab5f00] to-white h-screen w-64 shadow-lg">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 p-4 border-b border-white/30">
        <img src="/pasumai.png" alt="Pasumai Logo" class="h-10 w-10 object-contain">
        <span class="text-lg font-bold text-white">Pasumaikudil</span>
    </a>

    <!-- Sidebar Menu -->
    <ul class="flex flex-col gap-1 mt-4 text-sm font-medium text-gray-100" x-data="{ openGrocery: true }">

        <!-- Dashboard -->
        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-4 py-2 rounded transition
                      hover:bg-[#ab5f00]/90
                      @if(request()->routeIs('admin.dashboard')) bg-[#ab5f00] text-white @endif">
                <i class="fa fa-home w-5"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Grocery Menu -->
        <li>
            <button @click="openGrocery = !openGrocery"
                    class="flex items-center justify-between w-full px-4 py-2 rounded transition hover:bg-[#ab5f00]/90">
                <div class="flex items-center gap-3">
                    <i class="fa fa-shopping-cart w-5"></i>
                    <span>Grocery</span>
                </div>
                <i :class="openGrocery ? 'fa fa-chevron-down' : 'fa fa-chevron-left'"></i>
            </button>

            <!-- Submenu -->
            <ul x-show="openGrocery" x-transition class="ml-8 mt-1 space-y-1">
                <li>
                    <a href="#"
                       class="flex items-center gap-3 px-3 py-2 rounded transition hover:bg-[#ab5f00]/80">
                        <i class="fas fa-layer-group w-5"></i>
                        <span>Category</span>
                    </a>
                </li>
                <li>
                    <a href="#"
                       class="flex items-center gap-3 px-3 py-2 rounded transition hover:bg-[#ab5f00]/80">
                        <i class="fas fa-carrot w-5"></i>
                        <span>Vegetables</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Milk -->
        <li>
            <a href="#"
               class="flex items-center gap-3 px-4 py-2 rounded transition hover:bg-[#ab5f00]/90">
                <i class="fas fa-glass-whiskey w-5"></i>
                <span>Milk</span>
            </a>
        </li>

        <!-- Another Milk (if needed, maybe replace later) -->
        <li>
            <a href="#"
               class="flex items-center gap-3 px-4 py-2 rounded transition hover:bg-[#ab5f00]/90">
                <i class="fas fa-cheese w-5"></i>
                <span>Dairy Products</span>
            </a>
        </li>

    </ul>
</aside>
