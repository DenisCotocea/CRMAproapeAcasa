<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Lead;
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        // Search in Properties table
        $properties = Property::where('description', 'like', "%{$query}%")
            ->orWhere('county', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->orWhere('type', 'like', "%{$query}%")
            ->orWhere('tranzaction', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('price', 'like', "%{$query}%")
            ->orWhere('details', 'like', "%{$query}%")
            ->orWhere('comfort', 'like', "%{$query}%")
            ->orWhere('furnished', 'like', "%{$query}%")
            ->orWhere('heating', 'like', "%{$query}%")
            ->orWhere('balcony', 'like', "%{$query}%")
            ->orWhere('garage', 'like', "%{$query}%")
            ->orWhere('elevator', 'like', "%{$query}%")
            ->orWhere('parking', 'like', "%{$query}%")
            ->orWhere('availability_status', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->orWhere('usable_area', 'like', "%{$query}%")
            ->orWhere('land_area', 'like', "%{$query}%")
            ->orWhere('yard_area', 'like', "%{$query}%")
            ->orWhere('balcony_area', 'like', "%{$query}%")
            ->orWhere('interior_condition', 'like', "%{$query}%")
            ->get();

        // Search in Leads table
        $leads = Lead::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('company_name', 'like', "%{$query}%")
            ->orWhere('company_email', 'like', "%{$query}%")
            ->orWhere('cui', 'like', "%{$query}%")
            ->orWhere('company_address', 'like', "%{$query}%")
            ->orWhere('cnp', 'like', "%{$query}%")
            ->orWhere('county', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->orWhere('source', 'like', "%{$query}%")
            ->orWhere('priority', 'like', "%{$query}%")
            ->orWhere('status', 'like', "%{$query}%")
            ->orWhere('last_contact', 'like', "%{$query}%")
            ->orWhere('notes', 'like', "%{$query}%")
            ->orWhere('doc_attachment', 'like', "%{$query}%")
            ->get();

        return view('search.index', compact('query', 'properties', 'leads'));
    }
}
