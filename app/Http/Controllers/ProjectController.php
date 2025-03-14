<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
    public function index(Request $request)
    {
        $query = Project::query()
            ->with(['lead', 'assignedUser', 'approvedBy'])
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            });

        // Filter projects based on user role
        if (Auth::user()->role === 'sales') {
            // Sales users can only see their own projects
            $query->where('assigned_to', Auth::id());
        }
        // Admin and managers can see all projects

        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';

        $projects = $query->orderBy($sortField, $sortDirection)
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        $salesUsers = User::where('role', 'sales')->get();

        return Inertia::render('Projects/Index', [
            'projects' => [
                'data' => $projects->items(),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                ],
                'links' => $projects->linkCollection(),
            ],
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
            ],
            'salesUsers' => $salesUsers,
        ]);
    }

    public function create(Request $request)
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
            'lead_id' => $request->input('lead_id'),
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

        // Handle empty assigned_to
        if ($validated['assigned_to'] === '') {
            $validated['assigned_to'] = null;
        }

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

        $leads = Lead::where(function($query) use ($project) {
                $query->where('status', '!=', 'converted')
                      ->orWhere('id', $project->lead_id);
            })
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

        // Handle empty assigned_to
        if ($validated['assigned_to'] === '') {
            $validated['assigned_to'] = null;
        }

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
        if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
            return back()->with('error', 'Only managers or admins can approve projects.');
        }

        $project->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Project approved successfully');
    }

    public function reject(Project $project)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
            return back()->with('error', 'Only managers or admins can reject projects.');
        }

        $project->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Project rejected successfully');
    }

    public function convert(Project $project)
    {
        if ($project->status !== 'approved') {
            return back()->with('error', 'Only approved projects can be converted to customers.');
        }

        DB::beginTransaction();

        try {
            $lead = $project->lead;

            // Create customer
            $customer = Customer::create([
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
