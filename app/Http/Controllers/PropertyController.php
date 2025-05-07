<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Property;
use function Illuminate\Events\queueable;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::latest()->paginate(10);
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
                $path = $image->store('properties', 'public');
                $property->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('properties.index')->with('success', 'Property created successfully.');
    }

    public function show(Property $property)
    {
        return view('properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $users = User::all();
        return view('properties.edit', compact('property'), compact('users'));
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
}
