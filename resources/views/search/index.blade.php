<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search by ' . $query) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="ms-2 mb-2 mt-2 me-2 text-white">
                    <h3>Properties</h3>
                </div>
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    @if ($properties->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 py-10">
                            <p class="text-lg">{{ __('No properties available at the moment.') }}</p>
                        </div>
                    @else
                        <x-table>
                            <x-slot name="thead">
                                <th class="px-6 py-3">Photo</th>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Price</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Transaction</th>
                                <th class="px-6 py-3">City</th>
                                <th class="px-6 py-3">Promoted</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </x-slot>

                            @foreach ($properties as $property)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700" style="{{ $property->locked_by_user_id ? 'background-color: #897e7f;' : '' }}">
                                    <td class="px-6 py-4">
                                        @if ($property->images->isNotEmpty())
                                            <img src="{{ asset('storage/' . $property->images->first()->path) }}" alt="Property Image" class="w-16 h-16 object-cover rounded">
                                        @else
                                            <span class="text-gray-500">No image</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-medium">{{ $property->name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold {{ $property->promoted ? 'text-green-600' : '' }}">
                                            €{{ number_format($property->price, 2) }}
                                        </span>
                                        @if ($property->promoted)
                                            <span class="ml-2 px-2 py-1 text-xs text-white bg-green-500 rounded-full">Promoted</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ ucfirst($property->type) }}</td>
                                    <td class="px-6 py-4">{{ ucfirst($property->tranzaction) }}</td>
                                    <td class="px-6 py-4">{{ $property->city }}</td>
                                    <td class="px-6 py-4">
                                        @if ($property->promoted)
                                            <x-badge color="green">Yes</x-badge>
                                        @else
                                            <x-badge color="gray">No</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$property->availability_status === 'available' ? 'green' : ($property->availability_status === 'reserved' ? 'yellow' : 'red')">
                                            {{ ucfirst($property->availability_status) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="d-flex justify-between mb-2">
                                            <x-link-primary-button href="{{ route('properties.show', $property->id) }}">
                                                {{ __('Show') }}
                                            </x-link-primary-button>
                                            <x-link-primary-button href="{{ route('properties.edit', $property->id) }}">
                                                {{ __('Edit') }}
                                            </x-link-primary-button>
                                        </div>
                                        <div class="text-center">
                                            <form action="{{ route('properties.destroy', $property->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @endif
                </div>
            </div>

            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-5">
                <div class="ms-2 mb-2 mt-2 me-2 text-white">
                    <h3>Leads</h3>
                </div>
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    @if ($leads->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 py-10">
                            <p class="text-lg">{{ __('No leads available at the moment.') }}</p>
                        </div>
                    @else
                        <x-table>
                            <x-slot name="thead">
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Company</th>
                                <th class="px-6 py-3">Priority</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </x-slot>

                            @foreach ($leads as $lead)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-medium">{{ $lead->name }}</td>
                                    <td class="px-6 py-4">{{ $lead->email }}</td>
                                    <td class="px-6 py-4">{{ $lead->phone }}</td>
                                    <td class="px-6 py-4">{{ $lead->has_company ? $lead->company_name : 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$lead->priority === 'High' ? 'red' : ($lead->priority === 'Medium' ? 'yellow' : 'green')">
                                            {{ ucfirst($lead->priority) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$lead->status === 'New' ? 'green' : ($lead->status === 'In Progress' ? 'yellow' : ($lead->status === 'Closed' ? 'blue' : 'red'))">
                                            {{ ucfirst($lead->status) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="d-flex justify-between mb-2">
                                            <x-link-primary-button href="{{ route('leads.show', $lead->id) }}">
                                                {{ __('Show') }}
                                            </x-link-primary-button>
                                            <x-link-primary-button href="{{ route('leads.edit', $lead->id) }}">
                                                {{ __('Edit') }}
                                            </x-link-primary-button>
                                        </div>
                                        <div class="text-center">
                                            <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
