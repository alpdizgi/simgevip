@props([
    'name',
    'size' => 16,
])

@php
    $s = (int) $size;
    $isBrand = $name === 'whatsapp';
@endphp

@if ($isBrand)
    <svg {{ $attributes->merge([
        'class' => 'icon icon--whatsapp',
        'width' => $s,
        'height' => $s,
        'viewBox' => '0 0 24 24',
        'fill' => 'currentColor',
        'aria-hidden' => 'true',
        'focusable' => 'false',
    ]) }}>
        <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.48-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.22 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/>
        <path d="M12.04 2C6.55 2 2.1 6.45 2.1 11.94c0 1.76.46 3.48 1.34 5L2 22l5.2-1.36a9.9 9.9 0 004.84 1.23h.01c5.49 0 9.94-4.45 9.94-9.94S17.53 2 12.04 2zm0 18.15h-.01a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.09.81.82-3.01-.2-.31a8.2 8.2 0 01-1.26-4.37c0-4.53 3.69-8.21 8.22-8.21 2.2 0 4.26.86 5.81 2.41a8.16 8.16 0 012.41 5.8c0 4.53-3.69 8.21-8.22 8.21z"/>
    </svg>
@else
    <svg {{ $attributes->merge([
        'class' => 'icon icon--'.$name,
        'width' => $s,
        'height' => $s,
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.75',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'aria-hidden' => 'true',
        'focusable' => 'false',
    ]) }}>
        @switch($name)
            @case('heart')
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/>
                @break
            @case('user')
                <circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                @break
            @case('truck')
                <path d="M1 4h14v11H1z"/><path d="M15 9h4l3 3v3h-7V9z"/><circle cx="5.5" cy="18.5" r="2"/><circle cx="17.5" cy="18.5" r="2"/>
                @break
            @case('shield')
                <path d="M12 3l8 3v6c0 5-3.5 8.5-8 9.5C7.5 20.5 4 17 4 12V6l8-3z"/><path d="M9 12l2 2 4-4"/>
                @break
            @case('search')
                <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
                @break
            @case('cart')
                <path d="M6 7h15l-1.4 8.2a2 2 0 01-2 1.8H9a2 2 0 01-2-1.7L5.2 4H2"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17.5" cy="20" r="1.3"/>
                @break
            @case('chevron-up')
                <path d="M18 15l-6-6-6 6"/>
                @break
            @case('chevron-down')
                <path d="M6 9l6 6 6-6"/>
                @break
            @case('chevron-left')
                <path d="M15 18l-6-6 6-6"/>
                @break
            @case('chevron-right')
                <path d="M9 18l6-6-6-6"/>
                @break
            @case('arrow-up')
                <path d="M12 19V5"/><path d="M5 12l7-7 7 7"/>
                @break
            @case('arrow-right')
                <path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>
                @break
            @case('zoom-in')
                <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/>
                @break
            @case('zoom-out')
                <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M8 11h6"/>
                @break
            @case('maximize')
                <path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>
                @break
            @case('x')
                <path d="M6 6l12 12"/><path d="M18 6L6 18"/>
                @break
            @case('menu')
                <path d="M4 6h16M4 12h16M4 18h16"/>
                @break
            @case('sliders')
                <path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>
                @break
            @case('sort')
                <path d="M7 3v18M7 3l-3 3M7 3l3 3M17 21V3M17 21l-3-3M17 21l3-3"/>
                @break
            @case('plus')
                <path d="M12 5v14"/><path d="M5 12h14"/>
                @break
            @case('minus')
                <path d="M5 12h14"/>
                @break
            @case('tag')
                <path d="M20.6 13.4l-7.2 7.2a2 2 0 01-2.8 0L3 13V3h10l7.6 7.6a2 2 0 010 2.8z"/><circle cx="7.5" cy="7.5" r="1.2" fill="currentColor" stroke="none"/>
                @break
            @case('star')
                <path d="M12 3.5l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 15.9 7.2 18.4l.9-5.4L4.2 9.2l5.4-.8L12 3.5z"/>
                @break
            @case('spark')
                <path d="M12 3v4"/><path d="M12 17v4"/><path d="M3 12h4"/><path d="M17 12h4"/><path d="M5.6 5.6l2.8 2.8"/><path d="M15.6 15.6l2.8 2.8"/><path d="M18.4 5.6l-2.8 2.8"/><path d="M8.4 15.6l-2.8 2.8"/>
                @break
            @case('bag')
                <path d="M6 8h12l1 12H5L6 8z"/><path d="M9 8V6a3 3 0 016 0v2"/>
                @break
            @case('store')
                <path d="M3 10l2-6h14l2 6"/><path d="M3 10h18v10H3z"/><path d="M9 20v-6h6v6"/>
                @break
            @case('map-pin')
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                @break
            @case('phone')
                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                @break
            @case('mail')
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/>
                @break
            @default
                <circle cx="12" cy="12" r="8"/>
        @endswitch
    </svg>
@endif
