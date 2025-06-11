<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
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
                                <p class="text-2xl">{{ $users }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-4">
                    @if ($activeUsers->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 py-10">
                            <p class="text-lg">{{ __('No users available at the moment.') }}</p>
                        </div>
                    @else
                        <x-table>
                            <x-slot name="thead">
                                <th class="px-6 py-3">Position</th>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Properties assigned</th>
                                <th class="px-6 py-3">Leads assigned</th>
                                <th class="px-6 py-3">Register date</th>
                            </x-slot>

                            @foreach ($activeUsers as $index => $user)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4">{{ $activeUsers->firstItem() + $index }}</td>
                                    <td class="px-6 py-4">{{$user->name}}</td>
                                    <td class="px-6 py-4">{{$user->email}}</td>
                                    <td class="px-6 py-4">{{$user->properties->count()}}</td>
                                    <td class="px-6 py-4">{{$user->leads->count()}}</td>
                                    <td class="px-6 py-4">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </x-table>

                        <div class="mt-4 px-6">
                            {{ $activeUsers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
