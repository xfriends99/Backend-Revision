<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class CostsEstimateRequest extends FormRequest
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
            'service_type_id' => 'required|int|exists:service_types,id',
            'additional' => 'array',
            'additional.*' => 'int|exists:additionals,id',
            'coupon' => 'exists:coupons,code',
            'data' => 'required|array',
            'data.weight' => 'required|numeric|min: 0.001',
            'data.origin_country_code' => 'required|string|exists:countries,code',
            'data.destination_country_code' => 'required|string|exists:countries,code',
            'data.admin_level_1' => 'string',
            'data.admin_level_2' => 'string',
            'data.admin_level_3' => 'string',
            'data.zip_code' => 'string',
            'data.items' => 'array',
            'data.process' => 'required|in:SHIP,CONSOLIDATION'
        ];
    }

    public function attributes()
    {
        return [
            'service_type_id' => 'Tipo de servicio',
            'additional' => 'Adicionales',
            'coupon' => 'Cupón',
            'data.*.weight' => 'Peso',
        ];
    }

    protected function getValidatorInstance()
    {
        // Collect request fields
        $data = $this->all();
        $items = isset($data['data']) ? json_decode($data['data'], true) : null;

        // Update request
        $data['data'] = $items;
        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
