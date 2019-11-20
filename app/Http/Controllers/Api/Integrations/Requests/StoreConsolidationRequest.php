<?php

namespace App\Http\Controllers\Api\Integrations\Requests;

use App\Models\Purchase;
use App\Models\WorkOrder;
use App\Repositories\WorkOrderRepository;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;

class StoreConsolidationRequest extends ApiRequest
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
            'work_order_id' => 'required',
            'locker'        => 'required|exists:lockers,code',
            'weight'        => 'required|numeric|min:0.001',
            'height'        => 'required|numeric|min:0.01',
            'width'         => 'required|numeric|min:0.01',
            'length'        => 'required|numeric|min:0.01',
            'unit_system'   => 'required|in:metric,imperial'
        ];
    }

    public function messages()
    {
        return [
            'work_order_id.required' => 'The [work_order_id] field is required.',
            'work_order_id.exists'   => 'Work Order not found.',
            'locker.required'        => 'The [locker] field is required.',
            'locker.exists'          => 'Locker not found.',
            'weight.required'        => 'The [weight] field is required.',
            'weight.numeric'         => 'The [weight] field is not numeric.',
            'height.required'        => 'The [height] field is required.',
            'height.numeric'         => 'The [height] field is not numeric.',
            'width.required'         => 'The [width] field is required.',
            'width.numeric'          => 'The [width] field is not numeric.',
            'length.required'        => 'The [length] field is required.',
            'length.numeric'         => 'The [length] field is not numeric.',
            'unit_system.required'   => 'The [unit_system] field is required.',
            'unit_system.in'         => 'The [unit_system] field is can be metric or imperial.',
        ];
    }

    protected function getValidatorInstance()
    {
        logger('[Consolidation] Original');
        logger($this->all());

        $this->sanitizeInput();

        $validator = parent::getValidatorInstance();

        logger('[Consolidation] Request');
        logger($this->all());

        $validator->after(function (Validator $validator) {
            if (!$this->validateWorkOrderExistsAndBelongsToLocker()) {
                $validator->errors()->add('work_order_id', 'Work Order not found for this locker.');
            }

            if (!$this->validateIsConsolidatable()) {
                $validator->errors()->add('work_order_id', 'Work Order is not consolidatable.');
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
            'work_order_id' => $this->request->get('work_order_id'),
            'locker'        => Str::upper($this->request->get('locker')),
            'weight'        => round(floatval($weight), 3),
            'height'        => round(floatval($height), 2),
            'width'         => round(floatval($width), 2),
            'length'        => round(floatval($length), 2),
            'unit_system'   => $unit_system,
        ]);
    }

    private function validateWorkOrderExistsAndBelongsToLocker()
    {
        /** @var WorkOrderRepository $workOrderRepository */
        $workOrderRepository = app(WorkOrderRepository::class);

        /** @var WorkOrder $workOrder */
        $workOrder = $workOrderRepository->getById($this->offsetGet('work_order_id'));

        /** @var Purchase $purchase */
        foreach ($workOrder->purchases as $purchase) {
            if ($purchase->getUserLockerCode() != $this->offsetGet('locker')) {
                return false;
            }
        }

        $this->setWorkOrder($workOrder);

        return true;
    }

    private function validateIsConsolidatable()
    {
        return (!empty($this->workOrder) && $this->workOrder->isConsolidateType());
    }

    private function setWorkOrder(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }
}
