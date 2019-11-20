<?php

namespace App\Http\Controllers\Api\Integrations\Requests;

use App\Models\Purchase;
use App\Models\WorkOrder;
use App\Repositories\PurchaseRepository;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;

class StoreShipmentRequest extends ApiRequest
{
    /** @var WorkOrder */
    public $workOrder = null;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tracking'    => 'required',
            'locker'      => 'required|exists:lockers,code',
            'weight'      => 'required|numeric|min:0.001',
            'height'      => 'required|numeric|min:0.01',
            'width'       => 'required|numeric|min:0.01',
            'length'      => 'required|numeric|min:0.01',
            'unit_system' => 'required|in:metric,imperial'
        ];
    }

    public function messages()
    {
        return [
            'tracking.required'    => 'The [tracking] field is required.',
            'tracking.exists'      => 'Tracking not found.',
            'locker.required'      => 'The [locker] field is required.',
            'locker.exists'        => 'Locker not found.',
            'weight.required'      => 'The [weight] field is required.',
            'weight.numeric'       => 'The [weight] field is not numeric.',
            'height.required'      => 'The [height] field is required.',
            'height.numeric'       => 'The [height] field is not numeric.',
            'width.required'       => 'The [width] field is required.',
            'width.numeric'        => 'The [width] field is not numeric.',
            'length.required'      => 'The [length] field is required.',
            'length.numeric'       => 'The [length] field is not numeric.',
            'unit_system.required' => 'The [unit_system] field is required.',
            'unit_system.in'       => 'The [unit_system] field is can be metric or imperial.',
        ];
    }

    protected function getValidatorInstance()
    {
        logger('[Shipment] Original');
        logger($this->all());

        $this->sanitizeInput();

        $validator = parent::getValidatorInstance();

        logger('[Shipment] Formatted');
        logger($this->all());

        $validator->after(function (Validator $validator) {
            if (!$this->validateTrackingExistsAndBelongsToLocker()) {
                $validator->errors()->add('tracking', 'Tracking not found for this locker.');
            }

            if (!$this->validateIsShippable()) {
                $validator->errors()->add('work_order_id', 'Purchase is marked for consolidation.');
            }
        });

        return $validator;
    }

    private function sanitizeInput()
    {
        $weight = $this->request->get('weight');
        $height = $this->request->get('height');
        $width = $this->request->get('width');
        $length = $this->request->get('length');

        $unit_system = $this->request->get('unit_system', 'imperial');
        if ($unit_system == 'imperial') {
            $weight = $weight * 0.45359237; // lb to kg
            $height = $height * 2.54; // in to cm
            $width = $width * 2.54; // in to cm
            $length = $length * 2.54; // in to cm
            $unit_system = 'metric';
        }

        $this->merge([
            'tracking'    => Str::upper($this->request->get('tracking')),
            'locker'      => Str::upper($this->request->get('locker')),
            'weight'      => round(floatval($weight), 3),
            'height'      => round(floatval($height), 2),
            'width'       => round(floatval($width), 2),
            'length'      => round(floatval($length), 2),
            'unit_system' => $unit_system,
        ]);
    }

    private function validateTrackingExistsAndBelongsToLocker()
    {
        /** @var PurchaseRepository $purchaseRepository */
        $purchaseRepository = app(PurchaseRepository::class);

        /** @var Purchase $purchase */
        $purchase = $purchaseRepository->getByTracking($this->offsetGet('tracking'));

        if ($purchase && ($purchase->getUserLockerCode() == $this->offsetGet('locker'))) {
            if ($purchase->workOrder) {
                $this->setWorkOrder($purchase->workOrder);

                return true;
            }
        }

        return false;
    }

    private function validateIsShippable()
    {
        return (!empty($this->workOrder));
    }

    private function setWorkOrder(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }
}
