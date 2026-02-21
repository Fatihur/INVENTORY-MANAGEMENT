<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['stocks.warehouse', 'batches']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('min_stock', '>', (
                DB::table('stocks')->selectRaw('COALESCE(SUM(qty_on_hand), 0)')
                    ->whereColumn('stocks.product_id', 'products.id')
            ));
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['stocks.warehouse', 'batches']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products,code',
            'sku' => 'required|string|max:50|unique:products,sku',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'safety_stock' => 'nullable|integer|min:0',
            'target_stock' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:1',
            'track_batch' => 'boolean',
            'track_serial' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = $validated['code'] ?? $validated['sku'];

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product created successfully',
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products,code,'.$product->id,
            'sku' => 'sometimes|required|string|max:50|unique:products,sku,'.$product->id,
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'unit' => 'sometimes|required|string|max:20',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'safety_stock' => 'nullable|integer|min:0',
            'target_stock' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:1',
            'track_batch' => 'boolean',
            'track_serial' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (! array_key_exists('code', $validated) && array_key_exists('sku', $validated)) {
            $validated['code'] = $validated['sku'];
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
