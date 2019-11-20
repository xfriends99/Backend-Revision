<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\WebHookRequest;
use App\Models\PaymentGateway;
use App\Services\Cards\GatewayFactory;
use App\Traits\JsonApiResponse;
use App\Http\Controllers\Controller;
use App\Repositories\PaymentGatewayRepository;
use Exception;

class PaymentsController extends Controller
{
    use JsonApiResponse;

    /** @var  paymentGatewayRepository */
    protected $paymentGatewayRepository;

    /**
     * PaymentsController constructor.
     * @param PaymentGatewayRepository $paymentGatewayRepository
     */
    public function __construct(PaymentGatewayRepository $paymentGatewayRepository)
    {
        $this->paymentGatewayRepository = $paymentGatewayRepository;
    }

    /**
     * @param WebHookRequest $request
     * @param string $gateway
     * @return \Illuminate\Http\JsonResponse
     */
    public function notifications(WebHookRequest $request, $gateway)
    {
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $this->paymentGatewayRepository->getByKey($gateway);

        try {
            GatewayFactory::processConfirmation($request, $paymentGateway);
        } catch (Exception $exception) {
            logger("[{$gateway}] Exception processing notification");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse($exception->getMessage(), 500);
        }

        return self::success(['message' => 'Transaction status updated']);
    }

    
}
