<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['lead', 'project'])
            ->when($request->input('search'), function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('customer_id', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                return $query->where('is_active', $status === 'active');
            });

        $customers = $query->latest()->paginate(10)
            ->withQueryString()
            ->through(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'company_name' => $customer->company_name,
                    'email' => $customer->email,
                    'customer_id' => $customer->customer_id,
                    'is_active' => $customer->is_active,
                    'created_at' => $customer->created_at,
                    'services_count' => $customer->services()->where('status', 'active')->count(),
                ];
            });

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'lead',
            'project',
            'services.product'
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        $products = Product::where('is_active', true)->get();

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return to_route('customers.show', $customer)
            ->with('success', 'Customer updated successfully');
    }

    public function addService(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            $customer->services()->attach($validated['product_id'], [
                'price' => $validated['price'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'active',
            ]);

            return back()->with('success', 'Service added successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add service: ' . $e->getMessage());
        }
    }

    public function updateService(Request $request, Customer $customer, $serviceId)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,suspended,terminated',
        ]);

        try {
            $customer->services()->updateExistingPivot($serviceId, [
                'price' => $validated['price'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            return back()->with('success', 'Service updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update service: ' . $e->getMessage());
        }
    }

    public function removeService(Customer $customer, $serviceId)
    {
        try {
            $customer->services()->detach($serviceId);

            return back()->with('success', 'Service removed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove service: ' . $e->getMessage());
        }
    }
}
