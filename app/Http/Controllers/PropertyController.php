<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Image;
use function Illuminate\Events\queueable;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $properties = $this->filterProperties($request);

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        $users = User::all();
        return view('properties.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'promoted' => 'boolean',
            'type' => 'nullable|string',
            'category' => 'nullable|string',
            'tranzaction' => 'nullable|string',
            'room_numbers' => 'nullable|integer',
            'level' => 'nullable|integer',
            'floor' => 'nullable|integer',
            'total_floors' => 'nullable|integer',
            'surface' => 'nullable|numeric',
            'usable_area' => 'nullable|numeric',
            'land_area' => 'nullable|numeric',
            'yard_area' => 'nullable|numeric',
            'balcony_area' => 'nullable|numeric',
            'construction_year' => 'nullable|integer',
            'county' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'details' => 'nullable|string',
            'partitioning' => 'nullable|string',
            'comfort' => 'nullable|string',
            'furnished' => 'boolean',
            'heating' => 'nullable|string',
            'balcony' => 'boolean',
            'garage' => 'boolean',
            'elevator' => 'boolean',
            'parking' => 'boolean',
            'availability_status' => 'nullable|string',
            'available_from' => 'nullable|date',
            'locked_by_user_id' => 'nullable|exists:users,id',
            'locked_at' => 'nullable|date',
            'interior_condition' => 'nullable|string',
        ]);

        $property = Property::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('images', 'public');

                Image::create([
                    'entity_id' => $property->id,
                    'entity_type' => Property::class,
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('properties.index')->with('success', 'Property created successfully.');
    }

    public function show(Property $property)
    {
        $activities = $property->activities()->latest()->get();

        if ($property->locked_by_user_id && $property->locked_by_user_id !== auth()->id() && now()->diffInSeconds($property->locked_at) < 60) {
            return back()->withErrors(['Această proprietate este deja accesată de altcineva.']);
        }

        $property->update([
            'locked_by_user_id' => auth()->id(),
            'locked_at' => now(),
        ]);

        return view('properties.show', compact('property', 'activities'));
    }

    public function edit(Property $property)
    {
        $users = User::all();
        $images = $property->images;
        return view('properties.edit', compact('property', 'users', 'images'));
    }

    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'promoted' => 'boolean',
            'type' => 'nullable|string',
            'category' => 'nullable|string',
            'tranzaction' => 'nullable|string',
            'room_numbers' => 'nullable|integer',
            'level' => 'nullable|integer',
            'floor' => 'nullable|integer',
            'total_floors' => 'nullable|integer',
            'surface' => 'nullable|numeric',
            'usable_area' => 'nullable|numeric',
            'land_area' => 'nullable|numeric',
            'yard_area' => 'nullable|numeric',
            'balcony_area' => 'nullable|numeric',
            'construction_year' => 'nullable|integer',
            'county' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'details' => 'nullable|string',
            'partitioning' => 'nullable|string',
            'comfort' => 'nullable|string',
            'furnished' => 'boolean',
            'heating' => 'nullable|string',
            'balcony' => 'boolean',
            'garage' => 'boolean',
            'elevator' => 'boolean',
            'parking' => 'boolean',
            'availability_status' => 'nullable|string',
            'available_from' => 'nullable|date',
            'locked_by_user_id' => 'nullable|exists:users,id',
            'locked_at' => 'nullable|date',
            'interior_condition' => 'nullable|string',
        ]);

        $property->update($data);

        return redirect()->route('properties.index')->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('properties.index')->with('success', 'Property deleted successfully.');
    }

    public function unlock($id)
    {
        $property = Property::find($id);

        if ($property && $property->locked_by_user_id == auth()->id()) {
            $property->update([
                'locked_by_user_id' => null,
                'locked_at' => null,
            ]);
        }

        return response()->noContent();
    }

    public function filterProperties(Request $request)
    {
        $query = Property::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tranzaction')) {
            $query->where('tranzaction', $request->tranzaction);
        }

        if ($request->filled('rooms')) {
            $query->where('room_numbers', $request->rooms);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('usable_min') && $request->filled('usable_max')) {
            $query->whereBetween('usable_area', [$request->usable_min, $request->usable_max]);
        }

        if ($request->filled('construction_year_min') && $request->filled('construction_year_max')) {
            $query->whereBetween('construction_year', [$request->construction_year_min, $request->construction_year_max]);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('county')) {
            $query->where('county', 'like', '%' . $request->county . '%');
        }

        if ($request->filled('price_min') && $request->filled('price_max')) {
            $query->whereBetween('price', [$request->price_min, $request->price_max]);
        }

        if ($request->filled('promoted')) {
            $query->where('promoted', $request->promoted);
        }

        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }

        if ($request->filled('furnished')) {
            $query->where('furnished', $request->furnished);
        }

        if ($request->filled('heating')) {
            $query->where('heating', $request->heating);
        }

        if ($request->filled('comfort')) {
            $query->where('comfort', $request->comfort);
        }

        if ($request->filled('balcony')) {
            $query->where('balcony', true);
        }

        if ($request->filled('garage')) {
            $query->where('garage', true);
        }

        if ($request->filled('elevator')) {
            $query->where('elevator', true);
        }

        if ($request->filled('parking')) {
            $query->where('parking', true);
        }

        if ($request->filled('floor_min') && $request->filled('floor_max')) {
            $query->whereBetween('floor', [$request->floor_min, $request->floor_max]);
        }

        if ($request->filled('partitioning')) {
            $query->where('partitioning', $request->partitioning);
        }

        if ($request->filled('interior_condition')) {
            $query->where('interior_condition', $request->interior_condition);
        }

        if ($request->filled('available_from')) {
            $query->where('available_from', '>=', $request->available_from);
        }

        return $query->paginate(10);
    }
}
