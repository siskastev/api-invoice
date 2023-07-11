<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $queryStringArray = $request->only('per_page', 'keyword');
        $perPage = $queryStringArray['per_page'] ?? 10;
        $keyword = $queryStringArray['keyword'] ?? null;

        $currentPage = $request->query('page') ?: 1;

        $query = Customer::query();

        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $customers = $query
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'page', $currentPage);

        return response()->json([
            'messages' => "OK",
            'data' => $customers->items(),
            'metadata' => [
                'current_page' => $customers->currentPage(),
                'from' => $customers->firstItem(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'to' => $customers->lastItem(),
                'total' => $customers->total(),
            ],
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerRequest $request)
    {
        try {

            $customer = Customer::create($request->all());

            return response()->json([
                "message" => 'Customer created successfully',
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while creating the customer'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'message' => sprintf('%s customer not found!!', $id)
            ], 404);
        }

        return response()->json([
            "messages" => "OK",
            "data" => $customer
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomerRequest  $request
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCustomerRequest $request, string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'message' => sprintf('%s Customer not found!!', $id)
            ], 404);
        }

        try {

            $customer->update($request->all());

            return response()->json([
                "message" => 'Customer update successfully',
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while updating the customer'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'message' => sprintf('%s customer not found!!', $id)
            ], 404);
        }

        try {

            $customer->delete();

            return response()->json([
                "message" => sprintf('Customer %s delete successfully', $id),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while deleting the customer'
            ], 500);
        }
    }
}
