<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderApiController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'items.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }

        $orders = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        return response()->json([
            'success' => true,
            'data' => $salesOrder->load(['customer', 'customer.addresses', 'items.product', 'warehouse'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create([
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'status' => 'draft',
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $salesOrder->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $salesOrder->load(['customer', 'items.product']),
                'message' => 'Sales Order created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status, ['draft', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update order with status: ' . $salesOrder->status
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'sometimes|required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $salesOrder->update($validated);

            if (isset($validated['items'])) {
                $salesOrder->items()->delete();
                foreach ($validated['items'] as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'discount_percent' => $item['discount_percent'] ?? 0,
                    ]);
                }
            }

            $salesOrder->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $salesOrder->load(['customer', 'items.product']),
                'message' => 'Sales Order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirm(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft orders can be confirmed'
            ], 422);
        }

        $salesOrder->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
            'message' => 'Sales Order confirmed successfully'
        ]);
    }

    public function cancel(SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status, ['draft', 'confirmed', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel order with status: ' . $salesOrder->status
            ], 422);
        }

        $salesOrder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
            'message' => 'Sales Order cancelled successfully'
        ]);
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft' && $salesOrder->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete order with status: ' . $salesOrder->status
            ], 422);
        }

        $salesOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sales Order deleted successfully'
        ]);
    }
}
