@props(['name', 'label' => null])

<div class="mb-4 mt-4 flex items-center">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1"
        {{ old($name) == 1 ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 text-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700']) }}>
    <label for="{{ $name }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</label>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
