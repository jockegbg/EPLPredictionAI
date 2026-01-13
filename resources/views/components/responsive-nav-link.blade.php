@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-pl-green text-start text-base font-medium text-pl-green bg-pl-purple/50 focus:outline-none focus:text-pl-green focus:bg-pl-purple focus:border-pl-green transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-300 hover:text-white hover:bg-pl-purple/50 hover:border-pl-green focus:outline-none focus:text-white focus:bg-pl-purple/50 focus:border-pl-green transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>