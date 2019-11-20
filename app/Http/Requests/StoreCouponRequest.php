<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
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
            'description' => 'required',
            'coupon_classification_id' => 'required|exists:coupon_classifications,id',
            'amount' => 'required_without:percent|numeric',
            'percent' => 'required_without:amount|numeric',
            'max_amount' => 'required|numeric',
            'max_uses' => 'sometimes|integer',
            'user_id' => 'sometimes|exists:users,id',
            'valid_from' => 'sometimes|date',
            'valid_to' => 'sometimes|date'
        ];
    }

    public function attributes()
    {
        return [
            'description' => 'description',
            'coupon_classification_id' => 'classification',
            'amount' => 'amount',
            'percent' => 'percent',
            'max_amount' => 'max amount',
            'max_uses' => 'max uses',
            'user_id' => 'user',
            'valid_from' => 'valid from',
            'valid_to' => 'valid to',
        ];
    }

}
