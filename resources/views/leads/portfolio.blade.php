<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Portfolio Leads') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="mb-2 mt-2 me-2 text-end">
                    <x-link-primary-button href="{{ route('leads.create') }}" >
                        {{ __('Add Lead') }}
                    </x-link-primary-button>
                </div>
                <form method="GET" action="{{ route('leads.portfolioView') }}" id="filterForm">
                    <div class="filter-container p-2">
                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-2">
                            {{ __('Filter Options') }}
                        </h2>
                        <div class="row">
                            <div class="col-md-4">
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="filter[name]" value="{{ request('filter.name') }}" />
                            </div>
                            <div class="col-md-4">
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="filter[email]" value="{{ request('filter.email') }}" />
                            </div>
                            <div class="col-md-4">
                                <x-input-label for="phone" value="Phone" />
                                <x-text-input id="phone" name="filter[phone]" value="{{ request('filter.phone') }}" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="user_id" value="User" />
                                <x-select id="user_id" name="filter[user_id]" :options="$users->pluck('name', 'id')" />
                            </div>


                            <div class="col-md-4">
                                <x-input-label for="city" value="City" />
                                <x-text-input id="city" name="filter[city]" value="{{ request('filter.city') }}" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="county" value="County" />
                                <x-text-input id="county" name="filter[county]" value="{{ request('filter.county') }}" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="cnp" value="CNP" />
                                <x-text-input id="cnp" name="filter[cnp]" value="{{ request('filter.cnp') }}" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="date_of_birth" value="Date of Birth" />
                                <x-text-input id="date_of_birth" name="filter[date_of_birth]" type="date" value="{{ request('filter.date_of_birth') }}" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="last_contact" value="Last Contact" />
                                <x-text-input id="last_contact" name="filter[last_contact]" type="date" value="{{ request('filter.last_contact') }}" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="filter[status]" :selected="request('filter.status')" label="Status" :options="['New' => 'New', 'In Progress' => 'In Progress', 'Closed' => 'Closed', 'Lost' => 'Lost']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="filter[type]" :selected="request('filter.type')" label="Type" :options="['Sale' => 'Sale', 'Rent' => 'Rent']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="filter[role]" :selected="request('filter.role')" label="Role" :options="['Buyer' => 'Buyer', 'Owner' => 'Owner']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="filter[priority]" :selected="request('filter.priority')" label="Priority" :options="['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']" />
                            </div>
                        </div>
                    </div>

                    <div class="text-end p-2">
                        <x-link-primary-button href="{{ route('leads.portfolioView') }}">
                            {{ __('Reset filters') }}
                        </x-link-primary-button>
                        <x-danger-button type="submit">{{ __('Filter') }}</x-danger-button>
                    </div>
                </form>

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
                                <th class="px-6 py-3">Address</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Last contact</th>
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
                                        {{ $lead->county . " " . $lead->city}}
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$lead->status === 'New' ? 'green' : ($lead->status === 'In Progress' ? 'yellow' : ($lead->status === 'Closed' ? 'blue' : 'red'))">
                                            {{ ucfirst($lead->status) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $lead->last_contact ?? 'Not Contacted' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="d-flex mb-2 gap-2">
                                            <x-link-primary-button href="{{ route('leads.show', $lead->id) }}">
                                                {{ __('Show') }}
                                            </x-link-primary-button>
                                            <x-link-primary-button href="{{ route('leads.edit', $lead->id) }}">
                                                {{ __('Edit') }}
                                            </x-link-primary-button>

                                            @if(auth()->user()->hasRole('Admin') || $lead->user_id === auth()->id())
                                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                                                </form>
                                            @endif
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
