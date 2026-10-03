@props([
    'for' => null,
    'error' => false,
    'prefix' => null,
    'required' => false,
    'suffix' => null,
])

@php
    $tag = filled($for) ? 'label' : 'span';
@endphp

<{{ $tag }}
    @if (filled($for)) for="{{ $for }}" @endif
    {{ $attributes->class(['filament-forms-field-wrapper-label inline-flex items-center space-x-3 rtl:space-x-reverse']) }}
>
    {{ $prefix }}

    <span
        @class([
            'text-sm font-medium leading-4',
            'text-gray-700' => ! $error,
            'dark:text-gray-300' => (! $error) && config('forms.dark_mode'),
            'text-danger-700' => $error,
            'dark:text-danger-400' => $error && config('forms.dark_mode'),
        ])
    >
        {{ $slot }}@if ($required)<sup class="text-danger-700 whitespace-nowrap font-medium @if(config('forms.dark_mode')) dark:text-danger-400 @endif">*</sup>@endif
    </span>

    {{ $suffix }}
</{{ $tag }}>
