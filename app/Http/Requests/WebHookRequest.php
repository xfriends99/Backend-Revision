<?php

namespace App\Http\Requests;

use App\Models\PaymentGateway;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\GatewayFactory;
use Illuminate\Foundation\Http\FormRequest;
use Exception;

class WebHookRequest extends FormRequest
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

        if($key = $this->route('gateway')){

            /** @var PaymentGateway $paymentGateway */
            $paymentGateway = $paymentGatewayRepository->getByKey($key);

            return GatewayFactory::validateWebHookRequest($paymentGateway);
        } else {
            throw new Exception('Key is not defined');
        }
    }
}
