<nav class="bg-white w-full border-b border-gray-200 px-5 py-2 shadow-sm rounded-xl flex justify-between items-center">
    <!-- Left: Menu Icon -->
    <a href="#" class="text-gray-700 hover:text-gray-900">
        <i class="fas fa-bars text-xl"></i>
    </a>

    <!-- Right Side -->
    <div class="flex items-center gap-6">
        <ul class="flex items-center gap-4">

            <!-- Notifications -->
            <li class="relative">
                <a href="{{ route('notifications.index') }}" class="relative">
                    <i class="fa fa-bell text-xl text-red-600"></i>
                    @php
                        $unread = \App\Models\Notification::where(['status' => 0, 'role' => 1])->count();
                    @endphp
                    @if ($unread > 0)
                        <span
                            class="absolute top-0 right-0 bg-red-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $unread }}
                        </span>
                    @endif
                </a>
            </li>

            <!-- User Profile Dropdown -->
            <li class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" @click.outside="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg py-2 z-50"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <form method="POST" action="{{ route('admin.user_logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                            <i class="fas fa-sign-out-alt text-xl"></i> Logout
                        </button>
                    </form>
                </div>
            </li>

        </ul>
    </div>
</nav>

<!-- Add Alpine.js for dropdown -->
