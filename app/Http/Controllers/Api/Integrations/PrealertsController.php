<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Requests\EcuadorReceptacleRequest;
use App\Services\CorreosEcuador\Requests\ReceptacleCreateRequestService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\JsonApiResponse;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use Exception;

class PrealertsController extends Controller
{
    use JsonApiResponse;
    
    public function correos_del_ecuador_receptacle(EcuadorReceptacleRequest $request)
    {
        try {
            /** @var ReceptacleCreateRequestService $ecuadorReceptacleService */
            $ecuadorReceptacleService = new ReceptacleCreateRequestService($request->getContent());

            /** @var UpdateUserInfoResponse $response */
            $response = $ecuadorReceptacleService->request();

            if ($response->hasErrors()) {
                throw new Exception($response->getErrors());
            }
        } catch (Exception  $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse($exception->getMessage(), '500');
        }
        
        return self::success(['message' => $response->getMessage()]);
    }
}
