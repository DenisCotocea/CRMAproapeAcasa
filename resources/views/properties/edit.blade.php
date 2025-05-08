<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Property') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-4" method="POST" action="{{ route('properties.update', $property->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') <!-- Method to send PUT request for update -->

                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6">
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="name" type="text" value="{{ old('name', $property->name) }}" required />
                                <x-input-error for="name" />
                            </div>

                            <!-- Promoted Checkbox -->
                            <div class="col-md-2">
                                <x-checkbox name="promoted" label="Promoted" :checked="old('promoted', $property->promoted)" />
                            </div>

                            <!-- User -->
                            <div class="col-md-4">
                                <x-input-label for="user_id" value="User" />
                                <x-select id="user_id" name="user_id" :options="$users->pluck('name', 'id')" :selected="old('user_id', $property->user_id)" required />
                                <x-input-error for="user_id" />
                            </div>

                            <!-- Price -->
                            <div class="col-md-4">
                                <x-input-label for="price" value="Price" />
                                <x-text-input id="price" name="price" type="number" step="0.01" value="{{ old('price', $property->price) }}" />
                                <x-input-error for="price" />
                            </div>

                            <!-- Room Numbers -->
                            <div class="col-md-4">
                                <x-input-label for="room_numbers" value="Room Numbers" />
                                <x-text-input id="room_numbers" name="room_numbers" type="number" value="{{ old('room_numbers', $property->room_numbers) }}" />
                                <x-input-error for="room_numbers" />
                            </div>

                            <!-- Level -->
                            <div class="col-md-4">
                                <x-input-label for="level" value="Level" />
                                <x-text-input id="level" name="level" type="number" value="{{ old('level', $property->level) }}" />
                                <x-input-error for="level" />
                            </div>

                            <!-- Floor -->
                            <div class="col-md-4">
                                <x-input-label for="floor" value="Floor" />
                                <x-text-input id="floor" name="floor" type="number" value="{{ old('floor', $property->floor) }}" />
                                <x-input-error for="floor" />
                            </div>

                            <!-- Total Floors -->
                            <div class="col-md-4">
                                <x-input-label for="total_floors" value="Total Floors" />
                                <x-text-input id="total_floors" name="total_floors" type="number" value="{{ old('total_floors', $property->total_floors) }}" />
                                <x-input-error for="total_floors" />
                            </div>

                            <!-- Surface -->
                            <div class="col-md-4">
                                <x-input-label for="surface" value="Surface (m²)" />
                                <x-text-input id="surface" name="surface" type="number" step="0.01" value="{{ old('surface', $property->surface) }}" />
                                <x-input-error for="surface" />
                            </div>

                            <!-- Usable Area -->
                            <div class="col-md-4">
                                <x-input-label for="usable_area" value="Usable Area (m²)" />
                                <x-text-input id="usable_area" name="usable_area" type="number" step="0.01" value="{{ old('usable_area', $property->usable_area) }}" />
                                <x-input-error for="usable_area" />
                            </div>

                            <!-- Land Area -->
                            <div class="col-md-4">
                                <x-input-label for="land_area" value="Land Area (m²)" />
                                <x-text-input id="land_area" name="land_area" type="number" step="0.01" value="{{ old('land_area', $property->land_area) }}" />
                                <x-input-error for="land_area" />
                            </div>

                            <!-- Balcony Area -->
                            <div class="col-md-4">
                                <x-input-label for="balcony_area" value="Balcony Area (m²)" />
                                <x-text-input id="balcony_area" name="balcony_area" type="number" step="0.01" value="{{ old('balcony_area', $property->balcony_area) }}" />
                                <x-input-error for="balcony_area" />
                            </div>

                            <!-- Construction Year -->
                            <div class="col-md-4">
                                <x-input-label for="construction_year" value="Construction Year" />
                                <x-text-input id="construction_year" name="construction_year" type="number" value="{{ old('construction_year', $property->construction_year) }}" />
                                <x-input-error for="construction_year" />
                            </div>

                            <!-- County -->
                            <div class="col-md-4">
                                <x-input-label for="county" value="County" />
                                <x-text-input id="county" name="county" value="{{ old('county', $property->county) }}" />
                                <x-input-error for="county" />
                            </div>

                            <!-- City -->
                            <div class="col-md-4">
                                <x-input-label for="city" value="City" />
                                <x-text-input id="city" name="city" value="{{ old('city', $property->city) }}" />
                                <x-input-error for="city" />
                            </div>

                            <!-- Address -->
                            <div class="col-md-12">
                                <x-input-label for="address" value="Address" />
                                <x-text-input id="address" name="address" value="{{ old('address', $property->address) }}" />
                                <x-input-error for="address" />
                            </div>

                            <!-- Type -->
                            <div class="col-md-4">
                                <x-select name="type" label="Type" :options="['apartment' => 'Apartment', 'house' => 'House', 'land' => 'Land']" :selected="old('type', $property->type)" />
                            </div>

                            <!-- Category -->
                            <div class="col-md-4">
                                <x-select name="category" label="Category" :options="['residential' => 'Residential', 'commercial' => 'Commercial']" :selected="old('category', $property->category)" />
                            </div>

                            <!-- Tranzaction -->
                            <div class="col-md-4">
                                <x-select name="tranzaction" label="Tranzaction" :options="['sale' => 'Sale', 'rent' => 'Rent']" :selected="old('tranzaction', $property->tranzaction)" />
                            </div>

                            <!-- Description -->
                            <div class="col-md-6">
                                <x-textarea label="Description" name="description">{{ old('description', $property->description) }}</x-textarea>
                                <x-input-error for="description" />
                            </div>

                            <!-- Details -->
                            <div class="col-md-6">
                                <x-textarea label="Details" name="details">{{ old('details', $property->details) }}</x-textarea>
                                <x-input-error for="details" />
                            </div>

                            <!-- Partitioning -->
                            <div class="col-md-4">
                                <x-select name="partitioning" label="Partitioning" :options="['detached' => 'Detached', 'semi-detached' => 'Semi-Detached', 'open-space' => 'Open Space']" :selected="old('partitioning', $property->partitioning)" />
                            </div>

                            <!-- Comfort -->
                            <div class="col-md-4">
                                <x-select name="comfort" label="Comfort" :options="['1' => '1', '2' => '2', 'luxury' => 'Luxury']" :selected="old('comfort', $property->comfort)" />
                            </div>

                            <!-- Heating -->
                            <div class="col-md-4">
                                <x-select name="heating" label="Heating" :options="['central' => 'Central', 'individual' => 'Individual', 'none' => 'None']" :selected="old('heating', $property->heating)" />
                            </div>

                            <!-- Availability Status -->
                            <div class="col-md-4">
                                <x-select name="availability_status" label="Availability Status" :options="['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold']" :selected="old('availability_status', $property->availability_status)" />
                            </div>

                            <!-- Interior Condition -->
                            <div class="col-md-4">
                                <x-select name="interior_condition" label="Interior Condition" :options="['new' => 'New', 'renovated' => 'Renovated', 'needs-renovation' => 'Needs Renovation']" :selected="old('interior_condition', $property->interior_condition)" />
                            </div>

                            <!-- Available From -->
                            <div class="col-md-4">
                                <x-input-label for="available_from" value="Available From" />
                                <x-text-input id="available_from" name="available_from" type="date" value="{{ old('available_from', $property->available_from) }}" />
                                <x-input-error for="available_from" />
                            </div>

                            <!-- Furnished -->
                            <div class="col-md-2">
                                <x-checkbox name="furnished" label="Furnished" :checked="old('furnished', $property->furnished)" />
                            </div>

                            <!-- Balcony -->
                            <div class="col-md-2">
                                <x-checkbox name="balcony" label="Balcony" :checked="old('balcony', $property->balcony)" />
                            </div>

                            <!-- Garage -->
                            <div class="col-md-2">
                                <x-checkbox name="garage" label="Garage" :checked="old('garage', $property->garage)" />
                            </div>

                            <!-- Elevator -->
                            <div class="col-md-2">
                                <x-checkbox name="elevator" label="Elevator" :checked="old('elevator', $property->elevator)" />
                            </div>

                            <!-- Parking -->
                            <div class="col-md-2">
                                <x-checkbox name="parking" label="Parking" :checked="old('parking', $property->parking)" />
                            </div>

                            <div class="col-md-4">
                                <x-image-uploader :entityId="$property->id" :entityType="App\Models\Property::class" />
                            </div>

                            <div class="mt-4 text-end">
                                <x-primary-button>Update Property</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
