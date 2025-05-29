@props(['name', 'label' => null, 'options' => [], 'required' => false, 'disabled' => false])

<div>
    <x-input-label for="{{ $name }}" :value="$label" />

    <select id="{{ $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring focus:ring-indigo-500 dark:focus:ring-indigo-600']) }}>
        <option value=""  {{ $disabled ? 'disabled' : '' }}>Select an option</option>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected(old($name) == $value)>{{ $label }}</option>
        @endforeach
    </select>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
