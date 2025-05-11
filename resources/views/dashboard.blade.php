<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="row">
                <!-- Leads Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-bullseye text-4xl" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-xl">Leads</h3>
                                <p class="text-2xl">{{ $leads }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <!-- Bootstrap Icon for Properties -->
                                <i class="bi bi-house-door text-4xl" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-xl">Properties</h3>
                                <p class="text-2xl">{{ $properties }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Section -->
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <!-- Bootstrap Icon for Users -->
                                <i class="bi bi-person-circle text-4xl" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-xl">Users</h3>
                                <p class="text-2xl">{{ $leads }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
