<?php

namespace App\Http\Requests;

use App\Models\PaymentGateway;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\GatewayFactory;
use Illuminate\Foundation\Http\FormRequest;
use Exception;

class StoreCardRequest extends FormRequest
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
     * @throws Exception
     */
    public function rules()
    {
        /** @var PaymentGatewayRepository $paymentGatewayRepository */
        $paymentGatewayRepository = app(PaymentGatewayRepository::class);

        if($key = $this->get('key')){

            /** @var PaymentGateway $paymentGateway */
            $paymentGateway = $paymentGatewayRepository->getByKey($key);

            return GatewayFactory::validateRequest($paymentGateway);
        } else {
            return [
                'key' => 'required|exists:payment_gateways,key'
            ];
        }
    }
}
