<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
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
            'purchases'      => 'required|array',
            'purchases.*'    => 'exists:purchases,id',
            'additionals.*'  => 'exists:additionals,id',
            'service_id'     => 'required|exists:service_types,id'
        ];
    }

    public function formatPurchases()
    {
        // Collect request fields
        $data = $this->all();
        $items = collect(isset($data['purchases']) ? json_decode($data['purchases'], true): []);

        // Update request
        $data['purchases'] = $items->toArray();
        $this->getInputSource()->replace($data);
    }

    public function formatAdditionals()
    {
        // Collect request fields
        $data = $this->all();
        $items = collect(isset($data['additionals']) ? json_decode($data['additionals'], true): []);

        // Update request
        $data['additionals'] = $items->toArray();
        $this->getInputSource()->replace($data);
    }

    public function formatCoupon()
    {
        // Collect request fields
        $data = $this->all();
        $coupon = isset($data['coupon']) ? json_decode($data['coupon'], true) : null;

        // Update request
        if($coupon){
            $data['coupon'] = $coupon;
            $this->getInputSource()->replace($data);
        }
    }

    protected function getValidatorInstance()
    {
        logger('[Request] Original');
        logger($this->toArray());

        $this->formatPurchases();
        $this->formatCoupon();
        if($this->get('additionals')) $this->formatAdditionals();

        logger('[Request] Formatted');
        logger($this->toArray());

        return parent::getValidatorInstance();
    }
}
