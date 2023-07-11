<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
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

        $query = Product::query();

        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
            $query->orWhere('type', 'like', '%' . $keyword . '%');
        }

        $products = $query
            ->orderBy('created_at')
            ->paginate($perPage);

        return response()->json([
            'messages' => "OK",
            'data' => $products->items(),
            'metadata' => [
                'current_page' => $currentPage,
                'from' => $products->firstItem(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'to' => $products->lastItem(),
                'total' => $products->total(),
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
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        try {

            $product = Product::create($this->mappingPayloadRequest($request->all()));

            return response()->json([
                "message" => 'Product created successfully',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while creating the product'
            ], 500);
        }
    }

    private function mappingPayloadRequest(array $payloads): array
    {
        return [
            'name' => $payloads['name'],
            'type' => $payloads['type'],
            'qty' => $payloads['qty'],
            'unit_price' => $payloads['unit_price'],
            'total_price' => $payloads['qty'] * $payloads['unit_price']
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $product = product::find($id);

        if (!$product) {
            return response()->json([
                'message' => sprintf('%s product not found!!', $id)
            ], 404);
        }

        return response()->json([
            "messages" => "OK",
            "data" => $product
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProductRequest $request, string $id)
    {
        $product = product::find($id);

        if (!$product) {
            return response()->json([
                'message' => sprintf('%s product not found!!', $id)
            ], 404);
        }

        try {

            $product->update($this->mappingPayloadRequest($request->all()));

            return response()->json([
                "message" => 'Product update successfully',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while updating the product'
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
        $product = product::find($id);

        if (!$product) {
            return response()->json([
                'message' => sprintf('%s product not found!!', $id)
            ], 404);
        }

        try {

            $product->delete();

            return response()->json([
                "message" => sprintf('Product %s delete successfully', $id),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while deleting the product'
            ], 500);
        }
    }
}
