<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Exporters') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="row">
                <!-- Leads Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div class="mb-4">
                                <i class="bi bi-bullseye text-4xl" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('export.leads') }}">
                                    {{ __('Export leads') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div class="mb-4">
                                <!-- Bootstrap Icon for Properties -->
                                <i class="bi bi-house-door text-4xl" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('export.properties') }}">
                                    {{ __('Export properties') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div class="mb-4">
                                <!-- Bootstrap Icon for Users -->
                                <i class="bi bi-person-circle text-4xl" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('export.users') }}">
                                    {{ __('Export users') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
