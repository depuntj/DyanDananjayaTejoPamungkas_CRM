<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerController extends Controller
{
   public function index(Request $request)
    {
        $query = Customer::with(['services'])
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

        $customers = $query->latest()->paginate(10);
        Log::info('Customer query result', ['count' => $customers->count(), 'total' => $customers->total()]);


        return Inertia::render('Customer/Index', [
        'customers' => [
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
                'total' => $customers->total(),
                'per_page' => $customers->perPage(),
            ],
            'links' => $customers->linkCollection(),
        ],
        'filters' => $request->only(['search', 'status']),
    ]);
}

    public function show(Customer $customer)
    {
        $customer->load([
            'lead',
            'project',
            'services'
        ]);

        return Inertia::render('Customer/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        $products = Product::where('is_active', true)->get();

        return Inertia::render('Customer/Edit', [
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
            // Add proper error handling
            if (!$customer) {
                return back()->with('error', 'Customer not found');
            }

            $customer->services()->attach($validated['product_id'], [
                'price' => $validated['price'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => 'active',
            ]);

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Service added successfully');
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
            if (!$customer) {
                return back()->with('error', 'Customer not found');
            }

            if (!$customer->services()->where('id', $serviceId)->exists()) {
                return back()->with('error', 'Service not found for this customer');
            }

            $customer->services()->updateExistingPivot($serviceId, [
                'price' => $validated['price'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Service updated successfully');
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
