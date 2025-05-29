<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::paginate(10); // 10 tickets per page
        return view('tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        return view('tickets.show', compact('ticket'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    // Store a new ticket
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('tickets.show', $ticket);
    }

    // Update a ticket (e.g., change status)
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $ticket->update([
            'status' => $request->status,
        ]);

        return redirect()->route('tickets.show', $ticket);
    }

    /**
     * Delete a ticket and its associated comments
     */
    public function destroy(Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id && !Auth::user()->is_admin) {
            abort(403, 'You do not have permission to delete this ticket.');
        }

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket and associated comments deleted successfully');
    }
}
