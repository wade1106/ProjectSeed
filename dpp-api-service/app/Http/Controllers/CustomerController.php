<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'keyword' => $request->input('keyword'),
                'status' => $request->input('status', 'all'),
                'per_page' => $request->input('per_page', 15),
            ];

            $customers = $this->customerService->getAllCustomers($filters);

            return response()->json([
                'success' => true,
                'message' => 'Customers retrieved successfully',
                'data' => $customers->items(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'last_page' => $customers->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $customer = $this->customerService->createCustomer($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'data' => null,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $customer = $this->customerService->getCustomerById($id);

            return response()->json([
                'success' => true,
                'message' => 'Customer retrieved successfully',
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = $this->customerService->updateCustomer($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
                'data' => null,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->customerService->deleteCustomer($id);

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'data' => null
            ], 404);
        }
    }
}