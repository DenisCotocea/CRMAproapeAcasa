<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Image;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        $properties = QueryBuilder::for(Property::class)
            ->with(['user'])
            ->allowedFilters([
                'name',
                'type',
                'category',
                'tranzaction',
                'room_numbers',
                'floor',
                'total_floors',
                'usable_area',
                'land_area',
                'yard_area',
                'balcony_area',
                'construction_year',
                'county',
                'city',
                'address',
                'partitioning',
                'interior_condition',
                'comfort',
                'heating',
                'availability_status',
                'available_from',
                AllowedFilter::callback('price_min', function ($query, $value) {
                    $query->where('price', '>=', $value);
                }),
                AllowedFilter::callback('price_max', function ($query, $value) {
                    $query->where('price', '<=', $value);
                }),
                AllowedFilter::callback('surface_min', function ($query, $value) {
                    $query->where('surface', '>=', $value);
                }),
                AllowedFilter::callback('surface_max', function ($query, $value) {
                    $query->where('surface', '<=', $value);
                }),
                AllowedFilter::callback('usable_area_min', function ($query, $value) {
                    $query->where('usable_area', '>=', $value);
                }),
                AllowedFilter::callback('usable_area_max', function ($query, $value) {
                    $query->where('usable_area', '<=', $value);
                }),
                AllowedFilter::exact('promoted'),
                AllowedFilter::exact('furnished'),
                AllowedFilter::exact('balcony'),
                AllowedFilter::exact('garage'),
                AllowedFilter::exact('elevator'),
                AllowedFilter::exact('parking'),
                AllowedFilter::exact('user_id'),
            ])
            ->where('from_scraper', '')
            ->paginate(10);

        return view('properties.index', compact('properties', 'users'));
    }

    public function scraperView(Request $request)
    {
        $properties = QueryBuilder::for(Property::class)
            ->allowedFilters([
                'name',
                'type',
                'category',
                'tranzaction',
                'room_numbers',
                'floor',
                'total_floors',
                'usable_area',
                'land_area',
                'yard_area',
                'balcony_area',
                'construction_year',
                'county',
                'city',
                'address',
                'partitioning',
                'interior_condition',
                'comfort',
                'heating',
                'availability_status',
                'available_from',
                AllowedFilter::callback('price_min', function ($query, $value) {
                    $query->where('price', '>=', $value);
                }),
                AllowedFilter::callback('price_max', function ($query, $value) {
                    $query->where('price', '<=', $value);
                }),
                AllowedFilter::callback('surface_min', function ($query, $value) {
                    $query->where('surface', '>=', $value);
                }),
                AllowedFilter::callback('surface_max', function ($query, $value) {
                    $query->where('surface', '<=', $value);
                }),
                AllowedFilter::callback('usable_area_min', function ($query, $value) {
                    $query->where('usable_area', '>=', $value);
                }),
                AllowedFilter::callback('usable_area_max', function ($query, $value) {
                    $query->where('usable_area', '<=', $value);
                }),
            ])
            ->where('from_scraper', 'OLX')
            ->paginate(10);

        return view('properties.scraper', compact('properties'));
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
            'user_id' => 'required_if:role,admin|exists:users,id',
            'promoted' => 'boolean',
            'type' => 'required|string',
            'category' => 'nullable|string',
            'tranzaction' => 'nullable|string',
            'room_numbers' => 'nullable|integer',
            'floor' => 'nullable|integer',
            'total_floors' => 'nullable|integer',
            'surface' => 'nullable|numeric',
            'usable_area' => 'nullable|numeric',
            'land_area' => 'nullable|numeric',
            'yard_area' => 'nullable|numeric',
            'balcony_area' => 'nullable|numeric',
            'construction_year' => 'nullable|integer',
            'county' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'price' => 'required|numeric',
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

        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            $data['user_id'] = $user->id;
        }

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
            'user_id' => 'required_if:role,admin|exists:users,id',
            'promoted' => 'boolean',
            'type' => 'required|string',
            'category' => 'nullable|string',
            'tranzaction' => 'nullable|string',
            'room_numbers' => 'nullable|integer',
            'floor' => 'nullable|integer',
            'total_floors' => 'nullable|integer',
            'surface' => 'nullable|numeric',
            'usable_area' => 'nullable|numeric',
            'land_area' => 'nullable|numeric',
            'yard_area' => 'nullable|numeric',
            'balcony_area' => 'nullable|numeric',
            'construction_year' => 'nullable|integer',
            'county' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'price' => 'required|numeric',
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
}
