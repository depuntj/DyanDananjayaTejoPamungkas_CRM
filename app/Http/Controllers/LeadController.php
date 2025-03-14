<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('assignedUser');

        // Search functionality
        if ($request->input('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%");
            });
        }

        // Status filtering
        if ($request->input('status')) {
            $query->where('status', $request->input('status'));
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
                'search' => $request->input('search'),
                'status' => $request->input('status'),
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
        $lead->load(['assignedUser', 'projects']);

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
        Log::info('Lead update raw data:', [
            'assigned_to_raw' => $request->input('assigned_to'),
            'all_data' => $request->all()
        ]);

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

        if ($request->has('assigned_to')) {
            if ($request->input('assigned_to') === '' || $request->input('assigned_to') === null) {
                $validated['assigned_to'] = null;
            } else {
                $validated['assigned_to'] = (int) $request->input('assigned_to');
            }
        }

        Log::info('About to update lead with data:', [
            'assigned_to' => $validated['assigned_to']
        ]);

        $lead->update($validated);

        $lead->refresh();
        Log::info('After update, lead data is:', [
            'assigned_to' => $lead->assigned_to
        ]);

        return redirect()->route('leads.index')
            ->with('success', 'Lead updated successfully');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully');
    }
}
