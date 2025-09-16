<aside
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-64 opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="-translate-x-64 opacity-0"
    class="w-64 bg-white dark:bg-gray-800 border-r fixed md:static inset-y-0 left-0 z-50"
>
    <div class="p-4 text-lg font-bold text-gray-700 dark:text-gray-200">
        {{ config('app.name') }}
    </div>
    <nav class="mt-4">
        <ul class="space-y-2">
            <li>
                <a
                   class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">
                    Dashboard
                </a>
            </li>
                <li>
                    <a href="{{ route('view.category') }}" wire:navigate
                       class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">
                        Category
                    </a>
                </li>

            <li>
                <a
                   class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">
                    Settings
                </a>
            </li>
        </ul>
    </nav>
</aside>
