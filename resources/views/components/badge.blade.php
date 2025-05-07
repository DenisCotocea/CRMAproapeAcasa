@props(['color' => 'gray'])

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-{$color}-100 text-{$color}-800"]) }}>
    {{ $slot }}
</span>
