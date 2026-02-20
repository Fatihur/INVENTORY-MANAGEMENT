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
        $query = Product::with(['category', 'stocks']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('min_stock', '>', (
                DB::table('stocks')->selectRaw('COALESCE(SUM(qty), 0)')
                    ->whereColumn('stocks.product_id', 'products.id')
            ));
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'stocks.warehouse', 'batches'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:products,code',
            'sku' => 'nullable|string|unique:products,sku',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'track_batch' => 'boolean',
            'track_serial' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product created successfully'
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|unique:products,code,' . $product->id,
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'unit' => 'sometimes|required|string|max:50',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'track_batch' => 'boolean',
            'track_serial' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product updated successfully'
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
