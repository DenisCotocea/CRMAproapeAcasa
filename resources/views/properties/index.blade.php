<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Properties List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="mb-2 mt-2 me-2 text-end">
                    <x-link-primary-button href="{{ route('properties.create') }}">
                        {{ __('Add Property') }}
                    </x-link-primary-button>
                </div>

                <form method="GET" action="{{ route('properties.index') }}" id="filterForm">
                    <div class="filter-container p-2">
                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-2">
                            {{ __('Filter Options') }}
                        </h2>
                        <div class="row">
                            <div class="col-md-3">
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="filter[name]" value="{{ request('filter.name') }}" />
                            </div>

                            @if(isset($users))
                                <div class="col-md-3">
                                    <x-input-label for="user_id" value="User" />
                                    <x-select id="user_id" name="filter[user_id]" :options="$users->pluck('name', 'id')" />
                                </div>
                            @endif

                            <div class="col-md-3">
                                <x-input-label for="price_min" value="Min price" />
                                <x-text-input id="price_min" name="filter[price_min]" value="{{ request('filter.price_min') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="price_max" value="Max price" />
                                <x-text-input id="price_max" name="filter[price_max]" value="{{ request('filter.price_max') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="room_numbers" value="Room number" />
                                <x-text-input id="room_numbers" name="filter[room_numbers]" value="{{ request('filter.room_numbers') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="floor" value="Floor" />
                                <x-text-input id="floor" name="filter[floor]" value="{{ request('filter.floor') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="total_floors" value="Total Floors" />
                                <x-text-input id="total_floors" name="filter[total_floors]" value="{{ request('filter.total_floors') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="surface_min" value="Min surface (m²)" />
                                <x-text-input id="surface_min" name="filter[surface_min]" value="{{ request('filter.surface_min') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="surface_max" value="Max surface (m²)" />
                                <x-text-input id="surface_max" name="filter[surface_max]" value="{{ request('filter.surface_max') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="usable_area_min" value="Min usable area (m²)" />
                                <x-text-input id="usable_area_min" name="filter[usable_area_min]" value="{{ request('filter.usable_area_min') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="usable_area_max" value="Max usable area (m²)" />
                                <x-text-input id="usable_area_max" name="filter[usable_area_max]" value="{{ request('filter.usable_area_max') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="land_area" value="Land area (m²)" />
                                <x-text-input id="land_area" name="filter[land_area]" value="{{ request('filter.land_area') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="balcony_area" value="Balcony area (m²)" />
                                <x-text-input id="balcony_area" name="filter[balcony_area]" value="{{ request('filter.balcony_area') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="construction_year" value="Construction Year" />
                                <x-text-input id="construction_year" name="filter[construction_year]" value="{{ request('filter.construction_year') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="county" value="County" />
                                <x-text-input id="county" name="filter[county]" value="{{ request('filter.county') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="address" value="Address" />
                                <x-text-input id="address" name="filter[address]" value="{{ request('filter.address') }}" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[from_scraper]" label="Scraper"  :selected="request('filter.from_scraper')" :options="['OLX' => 'OLX', 'Storia' => 'Storia', 'Publi24' => 'Publi24']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[type]" label="Type" :selected="request('filter.type')" :options="['Apartament/Garsoniera' => 'Apartament/Garsoniera' , 'Apartament' => 'Apartament' , 'Garsoniera' => 'Garsoniera' , 'House' => 'House', 'Land' => 'Land']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[category]" label="Category" :selected="request('filter.category')" :options="['residential' => 'Residential', 'commercial' => 'Commercial']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[tranzaction]" label="Tranzaction" :selected="request('filter.tranzaction')" :options="['sale' => 'Sale', 'rent' => 'Rent']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[partitioning]" :selected="request('filter.partitioning')" label="Partitioning" :options="['detached' => 'Detached', 'semi-detached' => 'Semi-Detached', 'open-space' => 'Open Space']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[comfort]" label="Comfort" :selected="request('filter.comfort')" :options="['1' => '1', '2' => '2', 'luxury' => 'Luxury']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[heating]" label="Heating" :selected="request('filter.heating')" :options="['central' => 'Central', 'individual' => 'Individual', 'none' => 'None']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[availability_status]" label="Availability Status" :selected="request('filter.availability_status')" :options="['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold']" />
                            </div>

                            <div class="col-md-3">
                                <x-select name="filter[interior_condition]" :selected="request('filter.interior_condition')" label="Interior Condition" :options="['new' => 'New', 'renovated' => 'Renovated', 'needs-renovation' => 'Needs Renovation']" />
                            </div>

                            <div class="col-md-3">
                                <x-input-label for="available_from" value="Available From" />
                                <x-text-input id="available_from" name="filter[available_from]" type="date" value="{{ request('filter.available_from') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="text-end p-2">
                        <x-link-primary-button href="{{ route('properties.index') }}">
                            {{ __('Reset filters') }}
                        </x-link-primary-button>
                        <x-danger-button type="submit">{{ __('Filter') }}</x-danger-button>
                    </div>
                </form>

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
                                <th class="px-6 py-3">Address</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Comments</th>
                                <th class="px-6 py-3">From</th>
                                <th class="px-6 py-3">Actions</th>
                            </x-slot>

                            @foreach ($properties as $property)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700" style="{{ $property->locked_by_user_id ? 'background-color: #897e7f;' : '' }}">
                                    <td class="px-6 py-4">
                                        @if ($property->images->isNotEmpty())
                                            <img src="{{ asset('storage/' . $property->images->first()->path) }}" alt="Property Image" class="w-16 h-16 object-cover rounded">
                                        @else
                                            <img src="{{ asset('images/home.png') }}"  alt="Property Image" class="w-16 h-16 object-cover rounded">
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
                                    <td class="px-6 py-4">{{ $property->address }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$property->availability_status === 'available' ? 'green' : ($property->availability_status === 'reserved' ? 'yellow' : 'red')">
                                            {{ ucfirst($property->availability_status) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <x-badge :color="$property->comments->count() ? 'red' : 'white'">
                                            {{ $property->comments->count() ? 'Yes' : 'No' }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">{{ $property->from_scraper ?? 'CRM'}}</td>
                                    <td class="px-6 py-4">
                                        <div class="d-flex mb-2 gap-2">
                                            <x-link-primary-button href="{{ route('properties.show', $property->id) }}">
                                                {{ __('Show') }}
                                            </x-link-primary-button>
                                            @if(auth()->user()->hasRole('Admin') || $property->user_id === auth()->id())
                                                <x-link-primary-button href="{{ route('properties.edit', $property->id) }}">
                                                    {{ __('Edit') }}
                                                </x-link-primary-button>
                                            @endif

                                            @if(!$property->user_id)
                                                <x-link-primary-button href="{{ route('properties.assign', $property->id) }}">
                                                    {{ __('Assign') }}
                                                </x-link-primary-button>
                                            @endif

                                            @role('Admin')
                                                    <form action="{{ route('properties.destroy', $property->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                                                    </form>
                                            @endrole
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>

                        <div class="p-2">
                            {{ $properties->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
