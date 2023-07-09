<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\InvoiceSummaryProduct;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

    const TAX_INVOICE = 0.1;
    const STATUS_UNPAID = 0;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreInvoiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceRequest $request)
    {
        $payloads = $request->all();
        $payloads['code'] = generateCode(new Invoice, "code", 7);

        $transaction = function () use ($payloads) {
            $payloadsInvoice = $this->mappingInvoices($payloads);
            $payloadsProducts = $this->mappingProducts($payloads);

            Invoice::create($payloadsInvoice);
            InvoiceSummaryProduct::insert($payloadsProducts);
        };

        try {
            DB::transaction($transaction);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to store invoice',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Invoice stored successfully',
            'data' => $payloads,
        ], 200);
    }

    private function mappingProducts(array $payloads): array
    {
        $data = [];

        foreach ($payloads['products'] as $value) {
            $data[] = [
                'invoice_code' => $payloads['code'],
                "product_id" => $value['product_id'],
                "product_name" => $value['product_name'],
                "qty" => $value['qty'],
                "unit_price" => $value['unit_price'],
                "total_price" => $value['qty'] * $value['unit_price'],
            ];
        }

        return $data;
    }

    private function mappingInvoices(array $payloads): array
    {
        $subTotal = $this->getSubTotal($payloads['products']);
        $taxAmount = $subTotal * self::TAX_INVOICE;

        return [
            'code' => $payloads['code'],
            'status' => self::STATUS_UNPAID,
            'subject' => $payloads['subject'],
            'issue_date' => $payloads['issue_date'],
            'due_date' => $payloads['due_date'],
            'customer_id' => $payloads['customer_id'],
            'total_items' => count($payloads['products']),
            'sub_total' => $subTotal,
            'tax' => $taxAmount,
            'grand_total' => $subTotal + $taxAmount,
        ];
    }

    private function getSubTotal(array $products): float
    {
        return array_sum(array_map(function ($product) {
            return $product['qty'] * $product['unit_price'];
        }, $products));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceRequest  $request
     * @param  string code invoice
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceRequest $request, string $code)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(invoice $invoice)
    {
        //
    }
}
