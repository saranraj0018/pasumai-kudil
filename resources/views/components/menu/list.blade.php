<div x-data="{ open: true }" class="w-full">
    <div class="w-full" @click="open = !open">
        {{ $trigger ?? ''}}
    </div>
    <div x-show="open" x-transition class="px-5">
        {{ $menus ?? '' }}
    </div>
</div>
