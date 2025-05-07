<div x-data="{ files: null }" class="p-1 border border-dashed rounded-md dark:border-gray-600">
    <input type="file" class="hidden" x-ref="fileInput" multiple @change="files = $refs.fileInput.files" {{ $attributes }}>

    <div class="flex items-center space-x-4">
        <button type="button" @click="$refs.fileInput.click()" class="'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-black dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150'">
           {{ __('Select Images') }}
        </button>

        <template x-if="files">
            <div class="ps-2 space-x-2 text-md text-white dark:text-white-400">
                <span x-text="files.length + ' files selected'"></span>
            </div>
        </template>
    </div>
</div>
