@if($images)
    <div class="row">
        @foreach($images as $image)
            <div class="col-md-2 mb-2">
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
