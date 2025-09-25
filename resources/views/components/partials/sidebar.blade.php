<aside class="bg-gradient-to-b from-[#ab5f00] to-white fixed top-0 left-0 h-full w-64 overflow-y-auto">
    <!-- Brand Logo -->
    <x-app-logo />

    <ul class="flex flex-col gap-3 mt-4 text-sm font-medium text-gray-100 ">
        <x-menu.item route="admin.dashboard" name="Dashboard" icon="fa-home" />

        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Grocery" icon="fa-shopping-cart" />
            </x-slot:trigger>

            <x-slot:menus>
                <x-menu.item route="view.category" name="Category" icon="fa-layer-group" />
                <x-menu.item name="Vegetables" icon="fa-carrot" />
                <x-menu.item route="products" name="Products" icon="fa-boxes" />
            </x-slot:menus>
        </x-menu.list>

        <x-menu.item name="Milk" icon="fa-glass-whiskey" />
        <x-menu.item name="Dairy Products" icon="fa-cheese" />
    </ul>
</aside>
