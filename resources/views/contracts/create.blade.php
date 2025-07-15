<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Contract') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <form class="p-4" method="POST" id="contractCreateForm" action="{{ route('contracts.store') }}" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="contract_type" value="{{ $type }}">

                        <div class="row">
                            @foreach($fields as $field)
                                <div class="col-md-4">
                                    <div class="mt-4">
                                        <x-input-label for="field_{{ $field->id }}" :value="$field->label" />

                                        <x-text-input
                                            id="field_{{ $field->id }}"
                                            name="fields[{{ $field->id }}]"
                                            :required="$field->required"
                                        />
                                        <x-input-error :for="'fields.' . $field->id" class="mt-2" />
                                    </div>
                                </div>
                            @endforeach
                            <div class="mt-4">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <x-input-label for="signature_agent" value='Semnatura Agent' />
                                        <canvas id="sigAgent" width="300" height="100" style="border:1px solid black; background: white"></canvas>
                                        <input type="hidden" name="signature_agent" id="signature_agent">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <x-input-label for="signature_client" value='Semnatura Client' />
                                        <canvas id="sigClient" width="300" height="100" style="border:1px solid black; background: white"></canvas>
                                        <input type="hidden" name="signature_client" id="signature_client">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 text-end">
                            <x-primary-button>Create Contract</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const sigClient = new SignaturePad(document.getElementById('sigClient'));
            const sigAgent = new SignaturePad(document.getElementById('sigAgent'));

            document.getElementById('#contractCreateForm').addEventListener('submit', function (e) {
                document.getElementById('signature_client').value = sigClient.isEmpty() ? '' : sigClient.toDataURL();
                document.getElementById('signature_agent').value = sigAgent.isEmpty() ? '' : sigAgent.toDataURL();
            });
        });
    </script>
</x-app-layout>
