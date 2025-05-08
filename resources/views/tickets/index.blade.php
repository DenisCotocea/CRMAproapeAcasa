<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ticket List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="mb-2 mt-2 me-2 text-end">
                    <x-link-primary-button href="{{ route('tickets.create') }}">
                        {{ __('Add Ticket') }}
                    </x-link-primary-button>
                </div>
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    @if ($tickets->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 py-10">
                            <p class="text-lg">{{ __('No tickets available at the moment.') }}</p>
                        </div>
                    @else
                        <x-table>
                            <x-slot name="thead">
                                <th class="px-6 py-3">Created By</th>
                                <th class="px-6 py-3">Title</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </x-slot>

                            @foreach ($tickets as $ticket)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-medium">{{ $ticket->user->name }}</td>
                                    <td class="px-6 py-4">{{ $ticket->title }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$ticket->status === 'Open' ? 'green' : ($ticket->status === 'In Progress' ? 'yellow' : ($ticket->status === 'Closed' ? 'blue' : 'red'))">
                                            {{ ucfirst($ticket->status) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="d-flex justify-between mb-2">
                                            <x-link-primary-button href="{{ route('tickets.show', $ticket->id) }}">
                                                {{ __('Show') }}
                                            </x-link-primary-button>
                                        </div>
                                        <div>
                                            <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="inline">
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
