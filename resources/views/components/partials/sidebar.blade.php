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
                <x-menu.item route="lists.products" name="Products" icon="fa-boxes" />
                <x-menu.item route="view.banner" name="Banner" icon="fa-image" />
                <x-menu.item route="view.coupons" name="Coupons" icon="fa-ticket-alt" />
                <x-menu.item route="view.orders" name="Orders" icon="fa-shopping-bag" />
                <x-menu.item route="view.users" name="Users" icon="fa-users" />
            </x-slot:menus>
        </x-menu.list>

        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Milk" icon="fa-glass-whiskey" />
            </x-slot:trigger>
            <x-slot:menus>
                <x-menu.item route="lists.users" name="User List" icon="fas fa-user"/>
                <x-menu.item route="view.milk.subscription" name="Subscription" icon="fa-calendar-check" />
            </x-slot:menus>
        </x-menu.list>
        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Hub" icon="fa-diagram-project" />
            </x-slot:trigger>
            <x-slot:menus>
                <x-menu.item name="Shipping" route="lists.shipping" icon="fa-solid fa-truck" />
            </x-slot:menus>
        </x-menu.list>
    </ul>
</aside>
