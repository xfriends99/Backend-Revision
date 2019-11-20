<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\WeightUnit;
use App\Repositories\WeightUnitRepository;
use App\Services\Purchases\WeightUnitConverter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePurchaseRequest extends FormRequest
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
            'marketplace' => 'required',
            'warehouse_id' => 'required|exists:warehouses,id',
            'address' => 'required',
            'carrier' => 'required',
            'value' => 'required|numeric',
            'type' => 'required',
            'tracking' => 'required|unique:purchases',
            'file' => 'required',
            'terms' => 'required',
            'weight_unit_id' => 'required|exists:weight_units,id',
            'service_id' => 'required|exists:service_types,id'
        ];
    }

    public function attributes()
    {
        return [
            'marketplace'      => 'Proveedor',
            'warehouse_id'     => 'Casillero',
            'address'          => 'Direccion de envio',
            'carrier'          => 'Transportista',
            'value'            => 'Valor USD',
            'type'             => 'Tipo de envio',
            'tracking'         => 'Numero de seguimiento',
            'purchased_at'     => 'Fecha de compra',
            'file'             => 'Factura de compra',
            'is_mobile_device' => 'es teléfono',
            'terms' => 'Terminos y condiciones',
            'weight_unit_id' => 'Unidad de peso',
            'service_id' => 'Servicio',
            'coupon' => 'Cupón',
            'coupon.code'    => 'Código de cupón',
            'coupon.coupon_classification_key' => 'Cupón clasificacióń'
        ];
    }

    public function formatTracking()
    {
        // Collect request fields
        $data = $this->all();

        // Convert to uppercase
        $data['tracking'] = isset($data['tracking']) ? Str::upper($data['tracking']) : null;

        $this->getInputSource()->replace($data);
    }

    public function formatItems()
    {
        // Collect request fields
        $data = $this->all();
        $items = collect(isset($data['items']) ? json_decode($data['items'], true) : []);

        // Make empty values null
        $items->transform(function ($item) {
            return collect($item)->transform(function ($v, $k) {
                return !empty($v) ? $v : null;
            })->toArray();
        });

        // Update request
        $data['items'] = $items->toArray();
        $this->getInputSource()->replace($data);
    }

    public function formatAddress()
    {
        // Collect request fields
        $data = $this->all();
        $address = isset($data['address']) ? json_decode($data['address'], true) : null;

        // Update request
        $data['address'] = $address;
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

    public function formatAdditionals()
    {
        // Collect request fields
        $data = $this->all();
        $items = collect(json_decode($data['additionals'], true));

        // Update request
        $data['additionals'] = $items->toArray();
        $this->getInputSource()->replace($data);
    }

    protected function getValidatorInstance()
    {
        logger('[Request] Original');
        logger($this->toArray());

        $this->formatTracking();
        $this->formatItems();
        $this->formatAddress();
        $this->formatCoupon();
        if ($this->get('additionals')) {
            $this->formatAdditionals();
        }

        logger('[Request] Formatted');
        logger($this->toArray());

        // Custom validation rules
        $validator = parent::getValidatorInstance();
        $validator->after(function (Validator $validator) {
            if (!$this->validatePurchaseAllowedWeightForMexico()) {
                $validator->errors()->add('weight_exceeded', 'El peso de la compra no puede superar los 8Kg.');
            }

            if (!$this->validatePurchaseAllowedWeightForPeru()) {
                $validator->errors()->add('weight_exceeded', 'El peso de la compra no puede superar los 2Kg.');
            }
        });

        return $validator;
    }

    public function calculateTotalWeight()
    {
        return collect($this->offsetGet('items'))->sum(function ($item) {
            return intval($item['quantity']) * floatval($item['weight']);
        });
    }

    private function validatePurchaseAllowedWeightForMexico()
    {
        /** @var User $user */
        $user = $this->user();
        if ($user->getCountryCode() == 'MX') {
            /** @var WeightUnit $weightUnit */
            $weightUnit = app(WeightUnitRepository::class)->getById($this->offsetGet('weight_unit_id'));

            return (new WeightUnitConverter($weightUnit, $this->calculateTotalWeight()))->getWeightAsKg() <= 8;
        }

        return true;
    }

    private function validatePurchaseAllowedWeightForPeru()
    {
        /** @var User $user */
        $user = $this->user();
        if ($user->getCountryCode() == 'PE') {
            /** @var WeightUnit $weightUnit */
            $weightUnit = app(WeightUnitRepository::class)->getById($this->offsetGet('weight_unit_id'));

            return (new WeightUnitConverter($weightUnit, $this->calculateTotalWeight()))->getWeightAsKg() <= 2;
        }

        return true;
    }
}
