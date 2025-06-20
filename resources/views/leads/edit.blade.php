<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit lead') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-4" method="POST" action="{{ route('leads.update', $lead->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="name" type="text" value="{{ old('name', $lead->name) }}" required />
                                <x-input-error for="name" />
                            </div>

                            <div class="col-md-2">
                                <x-checkbox name="has_company" label="Has Company?" :checked="old('has_company', (bool) $lead->has_company)" id="has_company"/>
                            </div>

                            @role('Admin')
                                <div class="col-md-4">
                                    <x-input-label for="user_id" value="User" />
                                    <x-select id="user_id" name="user_id" :options="$users->pluck('name', 'id')" :selected="old('user_id', $lead->user_id)" required/>
                                    <x-input-error for="user_id" />
                                </div>
                            @endrole

                            <div class="col-md-4">
                                <x-input-label for="properties" value="Property" />
                                <x-select id="properties" :tomSelect="true" name="properties"  :options="$properties->pluck('name', 'id')" :selected="old('property_id', $lead->property_id)"/>
                                <x-input-error for="properties" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="email" value="{{ old('email', $lead->email) }}"/>
                                <x-input-error for="email" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="phone" value="Phone" />
                                <x-text-input id="phone" name="phone" value="{{ old('phone', $lead->phone) }}" required/>
                                <x-input-error for="phone" />
                            </div>

                            <div id="company_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-input-label for="company_name" value="Company Name" />
                                        <x-text-input id="company_name" name="company_name" value="{{ old('company_name', $lead->company_name) }}" />
                                        <x-input-error for="company_name" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="company_email" value="Company Email" />
                                        <x-text-input id="company_email" name="company_email" value="{{ old('company_email', $lead->company_email) }}" />
                                        <x-input-error for="company_email" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="cui" value="Cui" />
                                        <x-text-input id="cui" name="cui" value="{{ old('cui', $lead->cui) }}" />
                                        <x-input-error for="cui" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="company_address" value="Company address" />
                                        <x-text-input id="company_address" name="company_address" value="{{ old('company_address', $lead->company_address) }}" />
                                        <x-input-error for="company_address" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="cnp" value="CNP" />
                                <x-text-input id="cnp" name="cnp" value="{{ old('cnp', $lead->cnp) }}" />
                                <x-input-error for="cnp"/>
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="date_of_birth" value="Date of Birth" />
                                <x-text-input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $lead->date_of_birth) }}" />
                                <x-input-error for="date_of_birth" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="county" value="County" />
                                <x-text-input id="county" name="county" value="{{ old('county', $lead->county) }}" required/>
                                <x-input-error for="county" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="city" value="City" />
                                <x-text-input id="city" name="city" value="{{ old('city', $lead->city) }}" required/>
                                <x-input-error for="city" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="last_contact" value="Last Contact" />
                                <x-text-input id="last_contact" name="last_contact" type="date" value="{{ old('last_contact', $lead->last_contact) }}" />
                                <x-input-error for="last_contact" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="status" label="Status" :options="['New' => 'New', 'In Progress' => 'In Progress', 'Closed' => 'Closed', 'Lost' => 'Lost']" :selected="old('status', $lead->status)" :disabled="true"/>
                            </div>

                            <div class="col-md-4">
                                <x-select name="type" label="Type" :options="['Sale' => 'Sale', 'Rent' => 'Rent']" :selected="old('type', $lead->type)" :disabled="true"/>
                            </div>


                            <div class="col-md-4">
                                <x-select name="role" label="Role" :options="['Buyer' => 'Buyer', 'Owner' => 'Owner']" :selected="old('role', $lead->role)" :disabled="true"/>
                            </div>

                            <div class="col-md-4">
                                <x-select name="priority" label="Priority" :options="['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']" :selected="old('priority', $lead->priority)" :disabled="true"/>
                            </div>

                            <div class="col-md-12">
                                <x-textarea label="Notes" name="notes">{{ old('notes', $lead->notes) }}</x-textarea>
                                <x-input-error for="notes" />
                            </div>

                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Upload Documents</label>
                                    <input type="file" name="doc_attachment"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                                  file:rounded file:border-0 file:text-sm file:bg-blue-50
                                                  file:text-blue-700 hover:file:bg-blue-100"
                                           accept=".pdf,.doc,.docx,.xlsx,.csv" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 text-end">
                            <x-primary-button>Edit Lead</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
