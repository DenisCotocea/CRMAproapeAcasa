<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create ticket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-4" method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <x-input-label for="title" value="Title" />
                                <x-text-input id="title" name="title" type="text" value="{{ old('title') }}" required />
                                <x-input-error for="title" />
                            </div>

                            <div class="col-md-6">
                                <x-select name="status" label="Status" :options="['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed']" />
                            </div>

                            <div class="col-md-12">
                                <x-textarea label="Description" name="description">{{ old('description') }}</x-textarea>
                                <x-input-error for="description" />
                            </div>
                        </div>
                        <div class="mt-6 text-end">
                            <x-primary-button>Create Ticket</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
