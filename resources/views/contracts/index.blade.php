<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Contracts List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-file-earmark-pdf" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('contracts.create', 'sale_unique') }}">
                                    {{ __('Unique Sale Contract') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-file-earmark-pdf" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('contracts.create', 'sale_collaboration') }}">
                                    {{ __('Collaboration Sale Contract') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-file-earmark-pdf" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('contracts.create', 'sale_exclusive') }}">
                                    {{ __('Exclusive Sale Contract') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-file-earmark-pdf" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('contracts.create', 'rent_owner') }}">
                                    {{ __('Owner Rent Contract') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center flex-column text-center">
                            <div>
                                <i class="bi bi-file-earmark-pdf" style="color: #00BDAF; font-size: 38px"></i>
                            </div>
                            <div>
                                <x-link-primary-button href="{{ route('contracts.create', 'rent_customer') }}">
                                    {{ __('Customer Rent Contract') }}
                                </x-link-primary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    @if ($contracts->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 py-10">
                            <p class="text-lg">{{ __('No contracts available at the moment.') }}</p>
                        </div>
                    @else
                        <x-table>
                            <x-slot name="thead">
                                <th class="px-6 py-3">Id</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Agent</th>
                                <th class="px-6 py-3">Has Leads</th>
                                <th class="px-6 py-3">Signed</th>
                                <th class="px-6 py-3">Actions</th>
                            </x-slot>

                            @foreach ($contracts as $contract)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-medium">{{ $contract->id }}</td>
                                    <td class="px-6 py-4">{{ $contract->contract_type }}</td>
                                    <td class="px-6 py-4">{{ $contract->agent->name }}</td>
                                    <td class="px-6 py-4">{{ $contract->leads ? 'Yes' : 'No' }}</td>
                                    <td class="px-6 py-4">{{ $contract->signed ? 'Yes' : 'No' }}</td>
{{--                                    <td class="px-6 py-4">--}}
{{--                                        <div class="d-flex mb-2 gap-2">--}}
{{--                                            <x-link-primary-button href="{{ route('contract.show', $contract->id) }}">--}}
{{--                                                {{ __('Show') }}--}}
{{--                                            </x-link-primary-button>--}}
{{--                                            <x-link-primary-button href="{{ route('contract.edit', $contract->id) }}">--}}
{{--                                                {{ __('Edit') }}--}}
{{--                                            </x-link-primary-button>--}}

{{--                                            @if(auth()->user()->hasRole('Admin') || $contract->user_id === auth()->id())--}}
{{--                                                <form action="{{ route('contract.destroy', $contract->id) }}" method="POST" class="inline">--}}
{{--                                                    @csrf--}}
{{--                                                    @method('DELETE')--}}
{{--                                                    <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>--}}
{{--                                                </form>--}}
{{--                                            @endif--}}
{{--                                        </div>--}}
{{--                                    </td>--}}
                                </tr>
                            @endforeach
                        </x-table>

                        <div class="mt-4">
                            {{ $contracts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
