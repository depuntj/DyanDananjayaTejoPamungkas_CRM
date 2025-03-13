<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('assignedUser')
            ->when(Auth::user()->role === 'sales', function ($query) {
                return $query->where('assigned_to', Auth::id())
                    ->orWhereNull('assigned_to');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
        ]);
    }

    public function create()
    {
        $salesUsers = User::where('role', 'sales')->get();

        return Inertia::render('Leads/Create', [
            'salesUsers' => $salesUsers,
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
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        Lead::create($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully');
    }

    public function show(Lead $lead)
    {
        $lead->load('assignedUser', 'projects');

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
        ]);
    }

    public function edit(Lead $lead)
    {
        $salesUsers = User::where('role', 'sales')->get();

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'salesUsers' => $salesUsers,
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
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead->update($validated);

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
