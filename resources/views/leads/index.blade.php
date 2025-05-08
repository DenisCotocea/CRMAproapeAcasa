<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Leads List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="mb-2 mt-2 me-2 text-end">
                    <x-link-primary-button href="{{ route('leads.create') }}">
                        {{ __('Add Lead') }}
                    </x-link-primary-button>
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

                        <div class="mt-4">
                            {{ $leads->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
