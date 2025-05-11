<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImagesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('images', 'public');

                Image::create([
                    'entity_id' => $request->entity_id,
                    'entity_type' => $request->entity_type,
                    'path' => $path,
                ]);
            }
        }

        return back();
    }

    public function destroy(Image $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back();
    }
}
