@props(['type' => 'button', 'varient' => 'primary'])

@php

    $styles = [
        'primary' => 'bg-[#d98c33] text-white hover:bg-[#d98c33]/90',
        'secondary' => 'bg-[#d98c33]/50 text-gray-800 hover:bg-gray-300',
        'outline' => 'border border-[#d98c33] text-[#d98c33] hover:bg-[#d98c33] hover:text-white',
        'danger' => 'bg-[#d7263d] text-white hover:bg-red-700',
        'ghost' => 'text-[#000] hover:bg-[#d98c33]/20',
        'link' => 'text-[#000] underline hover:text-gray-700',
    ];

    $bg = $styles[$varient];

@endphp

<button {{ $attributes->merge([]) }} class="{{ $bg }} text-[.9em] font-medium px-3 py-1 rounded-md transition" type="{{ $type }}">
    {{ $slot }}
</button>
