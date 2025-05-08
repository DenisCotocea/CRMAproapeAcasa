<form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-4">
        <input type="file" name="images[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
        <input type="hidden" name="entity_id" value="{{ $entityId }}">
        <input type="hidden" name="entity_type" value="{{ $entityType }}">
    </div>
</form>

@if(session('success'))
    <div class="mb-4 text-green-600">
        {{ session('success') }}
    </div>
@endif

@if($images)
    <div class="row">
        @foreach($images as $image)
            <div class="col-md-4">
                <img src="{{ Storage::url($image->path) }}" alt="Image" class="w-full h-32 object-cover rounded-md" />
                <form action="{{ route('images.destroy', $image->id) }}" method="POST" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                </form>
            </div>
        @endforeach
    </div>
@endif
