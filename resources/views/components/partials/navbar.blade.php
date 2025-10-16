<nav class="bg-white w-full border-b border-gray-200 px-5 py-3 shadow-sm rounded-xl flex justify-between items-center">
    <!-- Left: Menu Icon -->
    <a href="#" role="button" class="text-gray-700 hover:text-gray-900">
        <i class="fas fa-bars text-xl"></i>
    </a>

    <!-- Right: Logout Icon -->
    <form method="POST" action="{{ route('admin.user_logout') }}">
        @csrf
        <button type="submit" class="text-gray-700 hover:text-red-600 transition">
            <i class="fas fa-sign-out-alt text-xl"></i>LogOut
        </button>
    </form>
</nav>
