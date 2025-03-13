<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['lead', 'project'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load(['lead', 'project', 'services']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        $customer->load('services');
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

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

    public function manageServices(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.id' => 'required|exists:products,id',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.start_date' => 'required|date',
            'services.*.end_date' => 'nullable|date|after_or_equal:services.*.start_date',
            'services.*.status' => 'required|in:active,suspended,terminated',
        ]);

        DB::beginTransaction();

        try {
            // Sync services
            $syncData = [];
            foreach ($validated['services'] as $serviceData) {
                $syncData[$serviceData['id']] = [
                    'price' => $serviceData['price'],
                    'start_date' => $serviceData['start_date'],
                    'end_date' => $serviceData['end_date'] ?? null,
                    'status' => $serviceData['status'],
                ];
            }
            $customer->services()->sync($syncData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer services updated successfully');
    }

    public function addService(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $customer->services()->attach($validated['product_id'], [
            'price' => $validated['price'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'status' => 'active',
        ]);

        return back()->with('success', 'Service added successfully');
    }

    public function updateService(Request $request, Customer $customer, $serviceId)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,suspended,terminated',
        ]);

        $customer->services()->updateExistingPivot($serviceId, [
            'price' => $validated['price'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Service updated successfully');
    }

    public function removeService(Customer $customer, $serviceId)
    {
        $customer->services()->detach($serviceId);

        return back()->with('success', 'Service removed successfully');
    }
}
