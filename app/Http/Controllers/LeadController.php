<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('assignedUser');

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('company_name', 'like', $search);
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // If sales user, only show their leads or unassigned
        if (Auth::user()->role === 'sales') {
            $query->where(function($q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhereNull('assigned_to');
            });
        }

        $leads = $query->latest()->paginate(10);

        return Inertia::render('Leads/Index', [
            'leads' => [
                'data' => $leads->items(),
                'meta' => [
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'from' => $leads->firstItem(),
                    'to' => $leads->lastItem(),
                    'total' => $leads->total(),
                    'per_page' => $leads->perPage(),
                ],
                'links' => $leads->linkCollection(),
            ],
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function create()
    {
        $salesUsers = User::where('role', 'sales')->get();

        return Inertia::render('Leads/Create', [
            'salesUsers' => $salesUsers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:leads,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable',
        ]);

        if ($validated['assigned_to'] === '') {
            $validated['assigned_to'] = null;
        }

        $lead = Lead::create($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully');
    }

    public function show(Lead $lead)
{
    // Debug the assigned_to value before loading
    \Illuminate\Support\Facades\Log::info('Lead raw data', [
        'lead_id' => $lead->id,
        'assigned_to' => $lead->assigned_to,
        'assigned_to_type' => gettype($lead->assigned_to)
    ]);

    $lead->load(['assignedUser', 'projects']);

    // Debug after loading relationships
    \Illuminate\Support\Facades\Log::info('Lead with relationships', [
        'lead_id' => $lead->id,
        'assigned_to' => $lead->assigned_to,
        'assigned_user' => $lead->assignedUser
    ]);

    return Inertia::render('Leads/Show', [
        'lead' => $lead
    ]);
}

    public function edit(Lead $lead)
    {
        $salesUsers = User::where('role', 'sales')->get();

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'salesUsers' => $salesUsers
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:leads,email,' . $lead->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,lost,converted',
            'assigned_to' => 'nullable',
        ]);

        if ($validated['assigned_to'] === '') {
            $validated['assigned_to'] = null;
        }

        $lead->update($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead updated successfully');
    }

    public function destroy(Lead $lead)
    {
        if ($lead->projects()->exists() || $lead->status === 'converted') {
            return back()->with('error', 'This lead cannot be deleted because it has projects or has been converted');
        }

        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully');
    }
}
