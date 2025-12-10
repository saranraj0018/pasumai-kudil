@props(['route' => '', 'icon' => 'fa fa-home', 'name' => 'Dashboard', 'trigger' => false])

<li class="w-full">
    @if ($route)
        <a href="{{ $route ? route($route) : '#' }}"
            class="flex w-full text-white items-center gap-3 px-2 py-2 rounded transition hover:bg-[#ab5f00]/90 @if ($route && request()->routeIs($route)) bg-[#ab5f00] @endif">
            <i class="fa {{ $icon }} w-5"></i>
            <span>{{ $name }}</span>

            @if ($trigger)
                <i class="fa fa-chevron-down w-5"></i>
            @endif
        </a>
    @else
        <button type="submit"
            class="flex w-full text-white items-center gap-3 px-2 py-2 rounded transition hover:bg-[#ab5f00]/90 @if ($route && request()->routeIs($route)) bg-[#ab5f00] @endif ">

            <i class="fa {{ $icon }} w-5"></i>
            <span class="flex-1 text-left">{{ $name }}</span>

            @if ($trigger)
                <i class="fa fa-chevron-down w-5"></i>
            @endif
        </button>
    @endif
</li>
