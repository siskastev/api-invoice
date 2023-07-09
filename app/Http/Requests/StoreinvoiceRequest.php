<?php

namespace App\Http\Requests;

use App\Rules\ProductNameExist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject' => 'required',
            'issue_date' => 'required|date_format:Y-m-d|after_or_equal:'.now()->format("Y-m-d"),
            'due_date' => 'required|date_format:Y-m-d|after_or_equal:issue_date',
            'customer_id' => ['required', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'products' => 'required|array',
            'products.*.product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'products.*.product_name' => ['required', new ProductNameExist],
            'products.*.qty' => 'required|numeric|between:1,200',
            'products.*.unit_price' => 'required|numeric|min:1',
        ];
    }
}
