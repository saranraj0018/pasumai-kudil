<aside class="fixed top-0 left-0 h-full w-64 overflow-y-auto " style="background:#804300;">
    <!-- Brand Logo -->
    <x-app-logo />

    <ul class="flex flex-col gap-3 mt-4 text-sm font-medium text-[#e4c094] ">
        @can('view_dashboard')
         <x-menu.item route="admin.dashboard" name="Dashboard" icon="fa-home" />
        @endcan

        @can('view_grocery')
        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Grocery" icon="fa-shopping-cart" />
            </x-slot:trigger>
            <x-slot:menus>
                @can('view_category')
                <x-menu.item route="view.category" name="Category" icon="fa-layer-group" />
                @endcan
                @can('view_products')
                <x-menu.item route="lists.products" name="Products" icon="fa-boxes" />
                @endcan
                @can('view_coupons')
                <x-menu.item route="view.coupons" name="Coupons" icon="fa-ticket-alt" />
                @endcan
                @can('view_orders')
                <x-menu.item route="view.orders" name="Orders" icon="fa-shopping-bag" />
                @endcan
                @can('view_users')
                <x-menu.item route="view.users" name="Users" icon="fa-users" />
                 @endcan
                 @can('view_map')
                <x-menu.item route="show.map" name="Map" icon="fa-map-location-dot" />
                  @endcan

            </x-slot:menus>
        </x-menu.list>
        @endcan

        @can('view_milk')
        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Milk" icon="fa-glass-whiskey" />
            </x-slot:trigger>
            <x-slot:menus>
                @can('view_user_list')
                <x-menu.item route="lists.users" name="User List" icon="fas fa-user" />
                @endcan
                @can('view_subscription')
                <x-menu.item route="view.milk.subscription" name="Subscription" icon="fa-calendar-check" />
                @endcan
                 @can('view_delivery_partner')
                <x-menu.item name="Delivery Partner" route="lists.delivery_partner" icon="fa-solid fa-user-tie" />
                 @endcan
                 @can('view_delivery_list')
                <x-menu.item name="Delivery List" route="lists.delivery_list" icon="fa-solid fa-truck" />
                 @endcan
                 @can('view_today_delivery')
                <x-menu.item name="Today Delivery" route="lists.today_delivery_list" icon="fa-solid fa-box" />
                @endcan
            </x-slot:menus>
        </x-menu.list>
        @endcan
        @can('view_hub')
        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Hub" icon="fa-diagram-project" />
            </x-slot:trigger>
            <x-slot:menus>
                @can('view_banner')
                <x-menu.item route="view.banner" name="Banner" icon="fa-image" />
                @endcan
                @can('view_hub_list')
                <x-menu.item route="list.hub" name="Hub List" icon="fa-brands fa-hubspot" />
                @endcan
                @can('view_shipping')
                <x-menu.item name="Shipping" route="lists.shipping" icon="fa-solid fa-shipping-fast" />
                @endcan
                @can('view_ticket')
                <x-menu.item name="Ticket" route="ticket_lists" icon="fa-solid fa-ticket" />
                @endcan
            </x-slot:menus>
        </x-menu.list>
        @endcan

        @can('view_settings')
        <x-menu.list>
            <x-slot:trigger>
                <x-menu.item trigger name="Settings" icon="fa-diagram-project" />
            </x-slot:trigger>
            <x-slot:menus>
                @can('view_roles')
                <x-menu.item route="roles_list" name="Roles" icon="fa-image" />
                 @endcan
                @can('view_roles_permissions')
                <x-menu.item route="roles_and_permission" name="Role & Permissions" icon="fa-image" />
                @endcan
            </x-slot:menus>
        </x-menu.list>
        @endcan
    </ul>
</aside>
