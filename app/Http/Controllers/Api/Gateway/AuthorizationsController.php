<?php

namespace App\Http\Controllers\Api\Gateway;

use App\Models\PaymentGateway;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\GatewayFactory;
use App\Traits\JsonApiResponse;
use App\Http\Controllers\Controller;
use Exception;

class AuthorizationsController extends Controller
{
    use JsonApiResponse;

    /** @var  paymentGatewayRepository */
    protected $paymentGatewayRepository;
    
    
    public function __construct(PaymentGatewayRepository $paymentGatewayRepository)
    {
        $this->paymentGatewayRepository = $paymentGatewayRepository;
    }

    /**
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function show($key)
    {
        /** @var PaymentGateway $paymentGateway */
        if (!$paymentGateway = $this->paymentGatewayRepository->getByKey($key)) {
            return self::errorResponse('PaymentGateway no encontrado.', 404);
        }

        /** @var string $token */
        if ($token = GatewayFactory::token($paymentGateway)) {
            return self::success($token);
        }

        return self::errorResponse('Error al generar el token.', 500);
    }
}
