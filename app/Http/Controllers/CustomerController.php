<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer registered successfully',
            'customer' => $customer
        ], 201);
    }
    public function index()
    {
        $customers = Customer::all();

        return response()->json([
            'customers' => $customers
        ]);
    }
    // Update existing customer
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name'  => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ]);
    }


}
