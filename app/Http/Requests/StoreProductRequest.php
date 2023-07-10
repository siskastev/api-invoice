<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|min:3|max:100',
            'type' => 'required|in:service,hardware,subscription',
            'qty' => 'required|numeric|between:1,200',
            'unit_price' => 'required|numeric|min:1',
        ];
    }

    public function messages()
    {
        return [
            'type.in' => 'The selected type is invalid, Please choose type service, hardware, or subscription.',
        ];
    }
}
