<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="row">
                <!-- Leads Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-arrow-clockwise" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('clear.cache') }}">
                                    {{ __('Clear Cache') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
