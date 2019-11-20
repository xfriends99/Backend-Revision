<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 26/5/2019
 * Time: 11:16 PM
 */

namespace App\Services\CorreosEcuador\Requests;

use App\Models\Package;
use App\Repositories\PackageRepository;
use App\Services\CorreosEcuador\BaseRequestService;
use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use GuzzleHttp\Psr7\Request;
use Exception;

class ReceptacleCreateRequestService extends BaseRequestService
{
    private $request;

    /** @var  PackageRepository */
    private $packageRepository;

    public function __construct($request)
    {
        parent::__construct();
        
        $this->request = json_decode($request, true);
        $this->packageRepository = app(PackageRepository::class);
    }

    public function createRequest()
    {
        // TODO: Implement createRequest() method.

        foreach ($this->request['lista_paquetes'] as $index => $requestPackage) {
            /** @var Package $packageByTracking */
            $packageByTracking = $this->packageRepository->filter(['tracking' => $requestPackage['re_codigo_envio']])->first();

            if (!$packageByTracking) {
                throw new Exception("Package {$requestPackage['re_codigo_envio']} not found.");
            }

            $this->request['lista_paquetes'][$index]['pa_id'] = $packageByTracking->id_prealert;
        }

        return new Request('POST', 'api/Receptaculo', ['Content-Type' => 'application/json'], json_encode($this->request));
    }

    public function parse(HttpRequestEntity $httpRequestEntity)
    {
        // TODO: Implement parse() method.

        $response_parse = $httpRequestEntity->getResponseContentsAsJson();

        $message = '';

        if (isset($response_parse->mensaje)) {
            $message = $response_parse->mensaje;
        } elseif (isset($response_parse->Message)) {
            $message = $response_parse->Message;
        }

        if($httpRequestEntity->getStatusCode() == 200 && $response_parse->estado == 1){
            $response = new UpdateUserInfoResponse($message, false, $response_parse->id, $response_parse->estado);
        } else {
            $response = new UpdateUserInfoResponse($message, true);
        }

        $response->setHttpRequest($httpRequestEntity);

        return $response;
    }

}