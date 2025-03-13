<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['lead', 'assignedUser', 'approvedBy'])
            ->when(Auth::user()->role === 'sales', function ($query) {
                return $query->where('assigned_to', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        $leads = Lead::where('status', '!=', 'converted')
            ->when(Auth::user()->role === 'sales', function ($query) {
                return $query->where('assigned_to', Auth::id())
                    ->orWhereNull('assigned_to');
            })
            ->get();

        $salesUsers = User::where('role', 'sales')->get();
        $products = Product::where('is_active', true)->get();

        return Inertia::render('Projects/Create', [
            'leads' => $leads,
            'salesUsers' => $salesUsers,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lead_id' => 'required|exists:leads,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $project = Project::create([
                'name' => $validated['name'],
                'lead_id' => $validated['lead_id'],
                'status' => 'pending',
                'assigned_to' => $validated['assigned_to'] ?? Auth::id(),
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['products'] as $productData) {
                $project->products()->attach($productData['id'], [
                    'price' => $productData['price'],
                    'quantity' => $productData['quantity'],
                ]);
            }

            // Update lead status
            Lead::where('id', $validated['lead_id'])
                ->update(['status' => 'proposal']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully');
    }

    public function show(Project $project)
    {
        $project->load(['lead', 'assignedUser', 'approvedBy', 'products']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project)
    {
        $project->load('products');
        $leads = Lead::where('status', '!=', 'converted')
            ->orWhere('id', $project->lead_id)
            ->get();

        $salesUsers = User::where('role', 'sales')->get();
        $products = Product::where('is_active', true)->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'leads' => $leads,
            'salesUsers' => $salesUsers,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lead_id' => 'required|exists:leads,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        // Only managers can update projects that are already approved
        if ($project->status === 'approved' && Auth::user()->role !== 'manager') {
            return back()->with('error', 'You cannot edit an approved project.');
        }

        DB::beginTransaction();

        try {
            $project->update([
                'name' => $validated['name'],
                'lead_id' => $validated['lead_id'],
                'assigned_to' => $validated['assigned_to'],
                'notes' => $validated['notes'],
            ]);

            // Sync products
            $syncData = [];
            foreach ($validated['products'] as $productData) {
                $syncData[$productData['id']] = [
                    'price' => $productData['price'],
                    'quantity' => $productData['quantity'],
                ];
            }
            $project->products()->sync($syncData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully');
    }

    public function destroy(Project $project)
    {
        if ($project->status === 'approved') {
            return back()->with('error', 'You cannot delete an approved project.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully');
    }

    public function approve(Project $project)
    {
        if (Auth::user()->role !== 'manager') {
            return back()->with('error', 'Only managers can approve projects.');
        }

        $project->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Project approved successfully');
    }

    public function reject(Project $project)
    {
        if (Auth::user()->role !== 'manager') {
            return back()->with('error', 'Only managers can reject projects.');
        }

        $project->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Project rejected successfully');
    }

    public function convert(Request $request, Project $project)
    {
        if ($project->status !== 'approved') {
            return back()->with('error', 'Only approved projects can be converted to customers.');
        }

        DB::beginTransaction();

        try {
            $lead = $project->lead;

            // Create customer
            $customer = $lead->customer()->create([
                'name' => $lead->name,
                'company_name' => $lead->company_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'address' => $lead->address,
                'lead_id' => $lead->id,
                'project_id' => $project->id,
                'is_active' => true,
            ]);

            // Add services
            foreach ($project->products as $product) {
                $customer->services()->attach($product->id, [
                    'price' => $product->pivot->price,
                    'start_date' => now(),
                    'status' => 'active',
                ]);
            }

            // Update lead status
            $lead->update(['status' => 'converted']);

            // Update project status
            $project->update(['status' => 'completed']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Lead successfully converted to customer');
    }
}
