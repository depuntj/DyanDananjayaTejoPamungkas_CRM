<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['lead', 'project']);

        // Search functionality with improved search options
        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filtering
        if ($request->input('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        // Paginate with transformed data
        $customers = $query->latest()->paginate(10)
            ->through(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'company_name' => $customer->company_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
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
            'services' => function ($query) {
                $query->with('product')
                      ->withPivot('price', 'start_date', 'end_date', 'status');
            }
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        $products = Product::where('is_active', true)->get([
            'id', 'name', 'description', 'price', 'speed', 'type'
        ]);

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'required|email|unique:customers,email,' . $customer->id,
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'is_active' => 'boolean',
            ]);

            $customer->update($validated);

            Log::info('Customer updated', [
                'id' => $customer->id,
                'name' => $customer->name,
                'updated_by' => Auth::id()
            ]);

            return to_route('customers.show', $customer)
                ->with('success', 'Customer updated successfully');
        } catch (\Exception $e) {
            Log::error('Customer update failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to update customer'])->withInput();
        }
    }

    public function addService(Request $request, Customer $customer)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'price' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            $customer->services()->attach($validated['product_id'], [
                'price' => $validated['price'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'active',
            ]);

            Log::info('Service added to customer', [
                'customer_id' => $customer->id,
                'product_id' => $validated['product_id'],
                'added_by' => Auth::id()
            ]);

            DB::commit();
            return back()->with('success', 'Service added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add service', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to add service: ' . $e->getMessage()]);
        }
    }

    public function updateService(Request $request, Customer $customer, $serviceId)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|in:active,suspended,terminated',
            ]);

            $customer->services()->updateExistingPivot($serviceId, [
                'price' => $validated['price'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            Log::info('Customer service updated', [
                'customer_id' => $customer->id,
                'service_id' => $serviceId,
                'updated_by' => Auth::id()
            ]);

            DB::commit();
            return back()->with('success', 'Service updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update service', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to update service: ' . $e->getMessage()]);
        }
    }

    public function removeService(Customer $customer, $serviceId)
    {
        DB::beginTransaction();
        try {
            // Check if this is the last active service
            $activeServicesCount = $customer->services()->where('status', 'active')->count();

            $customer->services()->detach($serviceId);

            Log::info('Service removed from customer', [
                'customer_id' => $customer->id,
                'service_id' => $serviceId,
                'removed_by' => Auth::id()
            ]);

            DB::commit();
            return back()->with('success', 'Service removed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove service', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id,
                'service_id' => $serviceId
            ]);

            return back()->withErrors(['error' => 'Failed to remove service: ' . $e->getMessage()]);
        }
    }
}
