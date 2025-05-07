@props(['name', 'label' => null, 'required' => false])

<div class="mb-4">
    <x-input-label for="{{ $name }}" :value="$label" />

    <textarea id="{{ $name }}" name="{{ $name }}" rows="4" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'block mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring focus:ring-indigo-500 dark:focus:ring-indigo-600']) }}>{{ old($name) }}</textarea>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
