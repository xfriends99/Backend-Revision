<?php

namespace App\Services\Coupons\WebServices;

use App\Models\User;
use DOMDocument;
use Exception;

class ClubLaNacionService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $code;


    /**
     * ClubLaNacionService constructor.
     * @param User $user
     * @param string $code
     */
    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * @return object
     * @throws Exception
     */
    public function request()
    {
        // Prepare Request
        $request = $this->prepareRequest();
        logger('Club La Nacion  Web Service request');
        logger($request);

        try {
            $response = $this->performRequest($request);
            logger('Club La Nacion Web Service response');
            logger($response);

           return $this->parseResponse($response);
        } catch (Exception $e) {
            logger('Club La Nacion Web Service error');
            logger($e->getMessage());
            logger($e->getTraceAsString());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return array
     */
    protected function prepareRequest()
    {

        $params = [
            'docTipo' => strtoupper($this->user->getIdentificationTypeKey()),
            'numeroDoc' => $this->user->identification,
            'nroCredencial' => $this->code,
            'usr' => env('CLUB_LA_NACION_USER'),
            'tkn' => env('CLUB_LA_NACION_TOKEN')
        ];

        return $params;
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function performRequest($request)
    {
        $url = 'https://sws.lanacion.com.ar/WCFUsuario/Usuario.svc/ObtenerUsuarioClub';

        $headers = array(
            "Content-Type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        );

        $ch = curl_init();
        $data = http_build_query($request);
        $url = $url."?".$data;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param $response
     * @return object
     * @throws Exception
     */
    protected function parseResponse($response)
    {
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(simplexml_load_string($response));

        $responseCode = count($doc->getElementsByTagName('rta')) ? $doc->getElementsByTagName('rta')->item(0)->nodeValue : $doc->getElementsByTagName('RTA')->item(0)->nodeValue;

        if ($responseCode != 0) {
            throw new Exception("Code Error {$responseCode} - {$this->getErrorMessage($responseCode)}");
        }

        $id = $doc->getElementsByTagName('CRMID')->item(0)->nodeValue;
        $numCredential = $doc->getElementsByTagName('NUMCREDENCIAL')->item(0)->nodeValue;
        $tipoCredencial = $doc->getElementsByTagName('TIPOCREDENCIAL')->item(0)->nodeValue;

        return (object)['crmid' => $id, 'num_credencial' => $numCredential, 'tipo_credencial' => $tipoCredencial];
    }

    /**
     * @param int $code
     * @return string
     */
    protected function getErrorMessage(int $code)
    {
        $error = null;

        switch ($code)
        {
            case 1:
                 $error = 'El Tipo y Nro. Documento ingresado es Inexistente';
                break;
            case 2:
                $error = 'Credencial Inexistente';
                break;
            case 3:
                $error = 'Credencial Inactiva';
                break;
            case 4:
                $error = 'Los datos no corresponden al usuario';
                break;
            case 5:
                $error = 'Tipo y Nro Documento ingresado Inexistente:';
                break;
            case 6:
                $error = 'No existe credencial asocaida al usaurio';
                break;
            case 7:
                $error = 'Credencial inactiva para el usaurio';
                break;
            case 8:
                $error = 'Número de credencial inexistente';
                break;
            case 9:
                $error = 'Credencial Inactiva';
                break;
        }

        return $error;
    }

    /**
     * @param string $code
     * @return false|int
     */
    public function checkCouponcode(string $code)
    {
        return preg_match('/^[0-9\-]{18}+$/i', $code);
    }

}