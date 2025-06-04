@props(['name', 'label' => null, 'options' => [], 'selected' => null ,'required' => false, 'disabled' => false, 'placeholder' => 'Select an option', 'tomSelect' => false])

@php
    $selectClass = 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring focus:ring-indigo-500 dark:focus:ring-indigo-600';
    if ($tomSelect) {
        $selectClass .= ' tom-select';
    }
@endphp

<div>
    <x-input-label for="{{ $name }}" :value="$label" />

    <select id="{{ $name }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => $selectClass]) }}>


            @if (is_null($selected) || $selected === '')
                <option value="" disabled selected>{{ $placeholder }}</option>
            @endif

            @foreach ($options as $value => $text)
                <option value="{{ $value }}" {{ (string) $value === (string) $selected ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
    </select>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>

@if($tomSelect)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('.tom-select', {
                maxOptions: 1000,
                searchField: 'text',
                create: false,
                allowEmptyOption: true,
                placeholder: 'Select a property...',
            });
        });
    </script>
@endif
