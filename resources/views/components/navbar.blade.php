<header class="h-14 flex items-center justify-between px-4 bg-white border-b rounded-2xl">
    {{-- Sidebar toggle --}}
    <button @click="open = !open" class="text-gray-600 dark:text-gray-300 focus:outline-none">
        <!-- Hamburger Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>


    <div class="flex items-center space-x-4">
        <span class="text-gray-600 dark:text-gray-300">
            Hello, {{ Auth::guard('admin')->user()->name ?? 'Guest' }}
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-3 py-1 bg-[#ab5f00] text-white rounded">
                Logout
            </button>
        </form>
    </div>
</header>
