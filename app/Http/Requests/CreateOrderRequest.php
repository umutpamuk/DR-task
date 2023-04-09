<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'productItems' => 'required|array|min:1',
            'productItems.*.product_id' =>  [
                'required',
                Rule::exists('products', 'id')
            ],
            'productItems.*.product_quantity' => 'required|integer|min:1',
            'checkoutDetails' => 'required|array',
            'checkoutDetails.address' => 'required|string',
        ];
    }
}
