<aside
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-64 opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="-translate-x-64 opacity-0"
    class="w-64 bg-gradient-to-b from-[#ab5f00] to-[#ffffff] border-r fixed md:static inset-y-0 left-0 z-50"
>
    <a wire:navigate href="{{ route('view.dashboard') }}"
       class="flex items-center gap-3 p-4">
        <img src="/pasumai.png" alt="Pasumai Logo" style="height: 50px;">
        <span class="text-lg font-bold text-white">PasumaiKudil</span>
    </a>

    <nav class="mt-4 text-white">
        <ul class="space-y-2">
            <li>
                <a href="{{ route('view.dashboard') }}" wire:navigate
                   class="flex items-center gap-3 px-4 py-2 hover:bg-[#ab5f00] rounded">
                    <!-- Dashboard Icon -->
                    <i class="fa-solid fa-grip"></i>
                    Dashboard
                </a>
            </li>

            <li x-data="{ open: false }">
                <!-- Grocery parent -->
                <button @click="open = !open"
                        class="w-full flex items-center justify-between gap-3 px-4 py-2 hover:bg-[#ab5f00] rounded">
                    <div class="flex items-center gap-3">
                        <!-- Grocery Icon -->
                        <i class="fa-solid fa-cart-shopping"></i>
                        Grocery
                    </div>
                    <!-- Arrow icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform"
                         :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Submenu -->
                <ul x-show="open" x-transition class="ml-10 mt-2 space-y-2">
                    <li>

                        <a href="{{ route('view.category') }}" wire:navigate
                           class="block px-4 py-2 hover:bg-[#ab5f00] rounded">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-layer-group"></i>  Category
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#" wire:navigate
                           class="block px-4 py-2 hover:bg-[#ab5f00] rounded">
                            Vegetables
                        </a>
                    </li>
                </ul>
            </li>


            <li>
                <a  wire:navigate
                   class="flex items-center gap-3 px-4 py-2 hover:bg-[#ab5f00] rounded">
                    <!-- Milk Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3h8l1 4H7l1-4zm1 4v14a2 2 0 002 2h2a2 2 0 002-2V7H9z" />
                    </svg>
                    Milk
                </a>
            </li>

            <li>
                <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-[#ab5f00] rounded">
                    <!-- Settings Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 3.097-1.756 3.523 0a1.724 1.724 0 002.591 1.11c1.52-.885 3.392.987 2.506 2.507a1.724 1.724 0 001.11 2.59c1.757.426 1.757 3.097 0 3.523a1.724 1.724 0 00-1.11 2.591c.886 1.52-.986 3.392-2.506 2.506a1.724 1.724 0 00-2.591 1.11c-.426 1.757-3.097 1.757-3.523 0a1.724 1.724 0 00-2.59-1.11c-1.52.886-3.392-.986-2.507-2.506a1.724 1.724 0 00-1.11-2.591c-1.757-.426-1.757-3.097 0-3.523.427-.187.812-.481 1.11-2.59-.885-1.52.987-3.392 2.507-2.507a1.724 1.724 0 002.59-1.11z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </li>
        </ul>
    </nav>
</aside>
