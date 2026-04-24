<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'     => ['required', 'integer', 'exists:products,id'],
            'customer_name'  => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email'],
            'quantity'       => ['required', 'integer', 'min:1'],
        ];
    }
}
