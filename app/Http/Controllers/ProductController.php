<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Add search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Add type filtering
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $products = $query->latest()->paginate(10)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'speed' => $product->speed,
                    'type' => $product->type,
                    'is_active' => $product->is_active,
                    'created_at' => $product->created_at,
                ];
            });

        return Inertia::render('Products/Index', [
            'products' => [
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                ],
                'links' => $products->linkCollection(),
            ],
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:products,name',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'speed' => 'required|string|max:255',
                'type' => 'required|in:Residential,Business,Enterprise',
                'is_active' => 'boolean',
            ]);

            $product = Product::create($validated);

            Log::info('Product created', [
                'id' => $product->id,
                'name' => $product->name
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to create product'])->withInput();
        }
    }

    public function show(Product $product)
    {
        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:products,name,' . $product->id,
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'speed' => 'required|string|max:255',
                'type' => 'required|in:Residential,Business,Enterprise',
                'is_active' => 'boolean',
            ]);

            $product->update($validated);

            Log::info('Product updated', [
                'id' => $product->id,
                'name' => $product->name
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to update product'])->withInput();
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Check if product is associated with any active services or projects
            $hasAssociations = $product->customers()->exists() || $product->projects()->exists();

            if ($hasAssociations) {
                return back()->withErrors(['error' => 'Cannot delete product with active services or projects']);
            }

            $product->delete();

            Log::info('Product deleted', [
                'id' => $product->id,
                'name' => $product->name
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            Log::error('Product deletion failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);

            return back()->withErrors(['error' => 'Failed to delete product']);
        }
    }
}
