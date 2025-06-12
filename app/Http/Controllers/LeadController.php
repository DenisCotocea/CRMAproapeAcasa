<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class LeadController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();

        $leads = QueryBuilder::for(Lead::with(['user', 'properties']))
            ->allowedFilters([
                'name', 'email', 'phone', 'status', 'type', 'role' ,'priority', 'county', 'city',
                'source', 'company_name', 'company_email', 'cui',
                'company_address', 'cnp', 'date_of_birth',
                'last_contact', 'notes',
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('has_company'),
            ])
            ->allowedSorts(['name', 'email', 'created_at', 'priority'])
            ->paginate(10)
            ->appends($request->query());

        return view('leads.index', compact('leads', 'users'));
    }

    public function portfolioView(Request $request)
    {
        $users = User::all();

        $leads = QueryBuilder::for(Lead::with(['user', 'properties']))
            ->allowedFilters([
                'name', 'email', 'phone', 'status', 'type', 'role' ,'priority', 'county', 'city',
                'source', 'company_name', 'company_email', 'cui',
                'company_address', 'cnp', 'date_of_birth',
                'last_contact', 'notes',
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('has_company'),
            ])
            ->allowedSorts(['name', 'email', 'created_at', 'priority'])
            ->where('user_id', Auth::id())
            ->paginate(10)
            ->appends($request->query());

        return view('leads.portfolio', compact('leads', 'users'));
    }

    public function create()
    {
        $users = User::all();
        $properties = Property::all();
        return view('leads.create', compact('users'), compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required_if:role,admin|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'has_company' => 'boolean',
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'cui' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'cnp' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'county' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'source' => 'nullable|string|max:255',
            'priority' => 'required|in:High,Medium,Low',
            'status' => 'required|in:New,In Progress,Closed,Lost',
            'type' => 'required|in:Sale,Rent',
            'role' => 'required|in:Owner,Buyer',
            'last_contact' => 'nullable|date',
            'notes' => 'nullable|string',
            'doc_attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048'
        ]);

        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            $validated['user_id'] = $user->id;
        }

        if ($request->hasFile('doc_attachment')) {
            $file = $request->file('doc_attachment');
            $path = $file->store('docs', 'public');
            $validated['doc_attachment'] = $path;
        }

        $lead = Lead::create($validated);

        $lead->properties()->sync($request->properties);

        return redirect()->route('leads.portfolioView')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        $activities = $lead->activities()->latest()->get();
        return view('leads.show', compact('lead'), compact('activities'));
    }

    public function edit(Lead $lead)
    {
        $users = User::all();
        $properties = Property::all();
        return view('leads.edit', compact('lead', 'users', 'properties'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'user_id' => 'required_if:role,admin|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'has_company' => 'boolean',
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'cui' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'cnp' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'county' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'source' => 'nullable|string|max:255',
            'priority' => 'required|in:High,Medium,Low',
            'status' => 'required|in:New,In Progress,Closed,Lost',
            'type' => 'required|in:Sale,Rent',
            'role' => 'required|in:Owner,Buyer',
            'last_contact' => 'nullable|date',
            'notes' => 'nullable|string',
            'doc_attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048'
        ]);

        if ($request->hasFile('doc_attachment')) {
            $file = $request->file('doc_attachment');
            $path = $file->store('docs', 'public');
            $validated['doc_attachment'] = $path;
        }

        $lead->update($validated);

        $lead->properties()->sync($request->properties);

        return redirect()->route('leads.portfolioView')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        if ($lead->doc_attachment) {
            Storage::delete($lead->doc_attachment);
        }

        $lead->delete();

        return redirect()->route('leads.portfolioView')->with('success', 'Lead deleted successfully.');
    }
}
