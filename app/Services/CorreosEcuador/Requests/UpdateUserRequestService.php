<?php

namespace App\Services\CorreosEcuador\Requests;

use App\Models\User;
use App\Services\CorreosEcuador\BaseRequestService;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request as HttpRequest;

class UpdateUserRequestService extends BaseRequestService
{
    /** @var User */
    private $user;

    /** @var HttpRequest */
    private $request;


    /**
     * UserUpdateService constructor.
     * @param User $user
     * @param HttpRequest $request
     */
    public function __construct(User $user, HttpRequest $request)
    {
        parent::__construct();

        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @return Request
     */
    protected function createRequest()
    {
        $params = [
            "nombre_1"         => $this->user->getFirst1Name(),
            "nombre_2"         => $this->user->getFirst2Name(),
            "apellido_1"       => $this->user->getLast1Name(),
            "apellido_2"       => $this->user->getLast2Name(),
            "fecha_nacimiento" => $this->user->born_at,
            "cedula"           => $this->user->identification,
            "mail_personal"    => $this->request->get('email', $this->user->email),
            "password_md5"     => $this->user->getAuthPassword(),
            "celular"          => $this->request->get('phone', $this->user->phone),
            "convencional"     => $this->request->get('phone_conventional', $this->user->phone2),
            "ubi_geo_id"       => $this->request->get('ubi_geo_id'),
            "calle_1"          => $this->request->get('address1'),
            "numeracion"       => $this->request->get('number'),
            "calle_2"          => $this->request->get('address2'),
            "codigo_postal"    => $this->request->get('postal_code'),
            "casillero"        => $this->user->getLockerCode(),
            "referencia"       => $this->request->get('reference')
        ];

        return new Request('POST', "api/usuario", ['Content-Type' => 'application/json'], json_encode($params));
    }

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return UpdateUserInfoResponse
     */
    protected function parse(HttpRequestEntity $httpRequestEntity)
    {
        // Prepare json
        $json = $httpRequestEntity->getResponseContentsAsJson();

        // Check HTTP status code and return response
        if ($httpRequestEntity->getStatusCode() == 200) {
            $response = new UpdateUserInfoResponse($json->mensaje, false, $json->id_persona, $json->estado);
        } else {
            $response = new UpdateUserInfoResponse($json->Message, true);
        }

        $response->setHttpRequest($httpRequestEntity);

        return $response;
    }
}