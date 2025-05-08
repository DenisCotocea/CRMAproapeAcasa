<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Lead') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-4" method="POST" action="{{ route('leads.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="name" type="text" value="{{ old('name') }}" required />
                                <x-input-error for="name" />
                            </div>

                            <div class="col-md-2">
                                <x-checkbox name="has_company" label="Has Company?" id="has_company" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="user_id" value="User" />
                                <x-select id="user_id" name="user_id" :options="$users->pluck('name', 'id')" required />
                                <x-input-error for="user_id" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="properties" value="Property" />
                                <x-select id="properties" name="properties" :options="$properties->pluck('name', 'id')" required />
                                <x-input-error for="properties" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="email" value="{{ old('email') }}" />
                                <x-input-error for="email" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="phone" value="Phone" />
                                <x-text-input id="phone" name="phone" value="{{ old('phone') }}" />
                                <x-input-error for="phone" />
                            </div>

                            <div id="company_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-input-label for="company_name" value="Company Name" />
                                        <x-text-input id="company_name" name="company_name" value="{{ old('company_name') }}" />
                                        <x-input-error for="company_name" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="company_email" value="Company Email" />
                                        <x-text-input id="company_email" name="company_email" value="{{ old('company_email') }}" />
                                        <x-input-error for="company_email" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="company_phone" value="Company phone" />
                                        <x-text-input id="company_phone" name="company_phone" value="{{ old('company_phone') }}" />
                                        <x-input-error for="company_phone" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="cui" value="Cui" />
                                        <x-text-input id="cui" name="cui" value="{{ old('cui') }}" />
                                        <x-input-error for="cui" />
                                    </div>

                                    <div class="col-md-4">
                                        <x-input-label for="company_address" value="Company address" />
                                        <x-text-input id="company_address" name="company_address" value="{{ old('company_address') }}" />
                                        <x-input-error for="company_address" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="cnp" value="CNP" />
                                <x-text-input id="cnp" name="cnp" value="{{ old('cnp') }}" />
                                <x-input-error for="cnp"/>
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="date_of_birth" value="Date of Birth" />
                                <x-text-input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" />
                                <x-input-error for="date_of_birth" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="county" value="County" />
                                <x-text-input id="county" name="county" value="{{ old('county') }}" />
                                <x-input-error for="county" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="city" value="City" />
                                <x-text-input id="city" name="city" value="{{ old('city') }}" />
                                <x-input-error for="city" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="last_contact" value="Last Contact" />
                                <x-text-input id="last_contact" name="last_contact" type="date" value="{{ old('last_contact') }}" />
                                <x-input-error for="last_contact" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="status" label="Status" :options="['New' => 'New', 'In Progress' => 'In Progress', 'Closed' => 'Closed', 'Lost' => 'Lost']" />
                            </div>

                            <div class="col-md-4">
                                <x-select name="priority" label="Priority" :options="['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']" />
                            </div>

                            <div class="col-md-4">
                                <x-input-label for="notes" value="Notes" />
                                <x-text-input id="notes" name="notes" value="{{ old('notes') }}" />
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
                            <x-primary-button>Create Lead</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
