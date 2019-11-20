<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required|exists:states,id',
            'township' => 'required',
            'postal_code' => 'numeric',
        ];
    }

    public function attributes()
    {
        return [
            'address1' => 'dirección',
            'city' => 'ciudad',
            'state' => 'estado',
            'township' => 'municipio',
            'postal_code' => 'numeric',
        ];
    }
}
