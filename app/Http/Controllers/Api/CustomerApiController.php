<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'data' => $customer->load(['addresses', 'salesOrders'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer created successfully'
        ], 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer updated successfully'
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }
}
