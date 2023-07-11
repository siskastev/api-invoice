<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\InvoiceSummaryProduct;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filters = request()->only([
            'code',
            'issue_date',
            'due_date',
            'subject',
            'total_items',
            'customer_name',
            'status'
        ]);

        $currentPage = request()->query('page') ?: 1;

        $perPage = request()->query('per_page') ?: 10;

        $invoices = Invoice::getAll($filters, $currentPage, $perPage);

        return response()->json([
            'messages' => "OK",
            'data' => $invoices['data'],
            'metadata' => $invoices['metadata']
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
     * @param  \App\Http\Requests\StoreInvoiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceRequest $request)
    {
        $payloads = $request->all();
        $payloads['code'] = generateCode(new Invoice, "code", 7);

        $transaction = function () use ($payloads) {
            $payloadsInvoice = $this->mappingInvoices($payloads);
            Invoice::create($payloadsInvoice);

            $payloadsProducts = $this->mappingProducts($payloads);
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
        $taxAmount = $subTotal * Invoice::TAX_INVOICE;

        return [
            'code' => $payloads['code'],
            'status' => Invoice::STATUS_UNPAID,
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
     * @param  string $code
     * @return \Illuminate\Http\Response
     */
    public function show(string $code)
    {
        $invoice = Invoice::findByCode($code);

        if (!$invoice) {
            return response()->json([
                'message' => sprintf('%s invoice not found!!', $code)
            ], 404);
        }

        $invoice['products'] = InvoiceSummaryProduct::where('invoice_code', $code)->get();

        return response()->json([
            "messages" => "OK",
            "data" => $invoice
        ], 200);
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
     * @param  \App\Http\Requests\StoreInvoiceRequest  $request
     * @param  string $code
     * @return \Illuminate\Http\Response
     */
    public function update(StoreInvoiceRequest $request, string $code)
    {
        $invoice = $this->validatedInvoiceCode($code);

        if ($invoice instanceof JsonResponse) {
            return $invoice;
        }

        $payloads = $request->all();
        $payloads['code'] = $code;

        $transaction = function () use ($payloads, $invoice) {
            $payloadsProducts = $this->mappingProducts($payloads);
            InvoiceSummaryProduct::where('invoice_code', $payloads['code'])->delete();
            InvoiceSummaryProduct::insert($payloadsProducts);

            $payloadsInvoice = $this->mappingInvoices($payloads);
            unset($payloadsInvoice['code']);
            $invoice->update($payloadsInvoice);
        };

        try {
            DB::transaction($transaction);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Invoice update successfully',
            'data' => $payloads,
        ], 200);
    }

    private function validatedInvoiceCode(string $code)
    {
        $invoice = Invoice::firstWhere('code', $code);

        if (!$invoice) {
            return response()->json([
                'message' => sprintf('%s invoice not found!!', $code)
            ], 404);
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return response()->json([
                'message' => sprintf('%s invoice already PAID!!', $code)
            ], 400);
        }

        return $invoice;
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
