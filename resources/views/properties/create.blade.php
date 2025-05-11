<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Property') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-4" method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="name" type="text" value="{{ old('name') }}" required />
                                <x-input-error for="name" />
                            </div>

                            <div class="col-md-2">
                                <x-checkbox name="promoted" label="Promoted" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="user_id" value="User" />
                                <x-select id="user_id" name="user_id" :options="$users->pluck('name', 'id')" required />
                                <x-input-error for="user_id" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="price" value="Price" />
                                <x-text-input id="price" name="price" type="number" step="0.01" value="{{ old('price') }}" required/>
                                <x-input-error for="price" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="room_numbers" value="Room Numbers" />
                                <x-text-input id="room_numbers" name="room_numbers" type="number" value="{{ old('room_numbers') }}" />
                                <x-input-error for="room_numbers" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="level" value="Level" />
                                <x-text-input id="level" name="level" type="number" value="{{ old('level') }}" />
                                <x-input-error for="level" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="floor" value="Floor" />
                                <x-text-input id="floor" name="floor" type="number" value="{{ old('floor') }}" />
                                <x-input-error for="floor" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="total_floors" value="Total Floors" />
                                <x-text-input id="total_floors" name="total_floors" type="number" value="{{ old('total_floors') }}" />
                                <x-input-error for="total_floors" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="surface" value="Surface (m²)" />
                                <x-text-input id="surface" name="surface" type="number" step="0.01" value="{{ old('surface') }}" />
                                <x-input-error for="surface" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="usable_area" value="Usable Area (m²)" />
                                <x-text-input id="usable_area" name="usable_area" type="number" step="0.01" value="{{ old('usable_area') }}" />
                                <x-input-error for="usable_area" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="land_area" value="Land Area (m²)" />
                                <x-text-input id="land_area" name="land_area" type="number" step="0.01" value="{{ old('land_area') }}" />
                                <x-input-error for="land_area" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="balcony_area" value="Balcony Area (m²)" />
                                <x-text-input id="balcony_area" name="balcony_area" type="number" step="0.01" value="{{ old('balcony_area') }}" />
                                <x-input-error for="balcony_area" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="construction_year" value="Construction Year" />
                                <x-text-input id="construction_year" name="construction_year" type="number" value="{{ old('construction_year') }}" />
                                <x-input-error for="construction_year" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="county" value="County" />
                                <x-text-input id="county" name="county" value="{{ old('county') }}" required />
                                <x-input-error for="county" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="city" value="City" />
                                <x-text-input id="city" name="city" value="{{ old('city') }}" required/>
                                <x-input-error for="city" />
                            </div>

                            <div class="col-md-12">
                                <x-input-label for="address" value="Address" />
                                <x-text-input id="address" name="address" value="{{ old('address') }}" required/>
                                <x-input-error for="address" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="type" label="Type" :options="['apartment' => 'Apartment', 'house' => 'House', 'land' => 'Land']" required/>
                            </div>

                            <div class="col-md-4">
                                <x-select name="category" label="Category" :options="['residential' => 'Residential', 'commercial' => 'Commercial']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="tranzaction" label="Tranzaction" :options="['sale' => 'Sale', 'rent' => 'Rent']" />
                            </div>

                            <div class="col-md-6">
                                <x-textarea label="Description" name="description">{{ old('description') }}</x-textarea>
                                <x-input-error for="description" />
                            </div>

                            <div class="col-md-6">
                                <x-textarea label="Details" name="details">{{ old('details') }}</x-textarea>
                                <x-input-error for="details" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="partitioning" label="Partitioning" :options="['detached' => 'Detached', 'semi-detached' => 'Semi-Detached', 'open-space' => 'Open Space']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="comfort" label="Comfort" :options="['1' => '1', '2' => '2', 'luxury' => 'Luxury']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="heating" label="Heating" :options="['central' => 'Central', 'individual' => 'Individual', 'none' => 'None']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="availability_status" label="Availability Status" :options="['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold']" />
                            </div>

                            <div class="col-md-4">
                                <div class="col-span-1 md:col-span-4">
                                    <x-select name="interior_condition" label="Interior Condition" :options="['new' => 'New', 'renovated' => 'Renovated', 'needs-renovation' => 'Needs Renovation']" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="available_from" value="Available From" />
                                <x-text-input id="available_from" name="available_from" type="date" value="{{ old('available_from') }}" />
                                <x-input-error for="available_from" />
                            </div>

                            <div class="col-md-2">
                                <x-checkbox name="furnished" label="Furnished" />
                            </div>
                            <div class="col-md-2">
                                <x-checkbox name="balcony" label="Balcony" />
                            </div>
                            <div class="col-md-2">
                                <x-checkbox name="garage" label="Garage" />
                            </div>
                            <div class="col-md-2">
                                <x-checkbox name="elevator" label="Elevator" />
                            </div>
                            <div class="col-md-2">
                                <x-checkbox name="parking" label="Parking" />
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Upload Images</label>
                                    <input type="file" name="images[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 text-end">
                            <x-primary-button>Create Property</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
