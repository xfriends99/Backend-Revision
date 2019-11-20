<?php

namespace App\Services\Passport;

use App\Models\User;
use GuzzleHttp\Client;
use Laravel\Passport\Client as OauthClient;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;


class AccessTokenService
{
    /** @var ClientRepository */
    protected $clientRepository;

    /** @var TokenRepository */
    protected $tokenRepository;

    /**
     * GenerateAccessToken constructor.
     * @param ClientRepository $clientRepository
     * @param TokenRepository $tokenRepository
     */
    public function __construct(ClientRepository $clientRepository,
                                TokenRepository $tokenRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @param User $user
     * @return string
     */
    public function createFromUser(User $user)
    {
        /** @var OauthClient $oauthClient */
        $oauthClient = $this->clientRepository->find(env('VUE_API_CLIENT_ID'));

        /** @var string $access_token */
        $access_token = $this->generateAccessTokenForUserClient($user, $oauthClient);

        return $access_token;
    }

    public function disabledAccessTokenForUser(User $user)
    {
        /** @var OauthClient $oauthClient */
        $oauthClient = $this->clientRepository->find(env('VUE_API_CLIENT_ID'));

        /** @var Token $token */
        if($token = $this->tokenRepository->getValidToken($user, $oauthClient)){
            $this->tokenRepository->revokeAccessToken($token->id);
        }
    }

    /**
     * @param User $user
     * @param OauthClient $oauthClient
     * @return string
     */
    private function generateAccessTokenForUserClient(User $user, OauthClient $oauthClient)
    {
        $tokenObj = $user->createToken('Register Token');

        $tokenObj->token->update(['client_id' => $oauthClient->id]);

        /** @var string $access_token */
        $access_token = $tokenObj->accessToken;

        return $access_token;
    }

    /**
     * @param OauthClient $oauthClient
     * @return mixed
     */
    private function generateAccessTokenForClient(OauthClient $oauthClient)
    {
        /** @var Client $clientHttp */
        $clientHttp = new Client();

        $base_uri = (app()->environment() == 'local') ? 'http://casilleros_nginx' : env('APP_URL');
        $url = $base_uri . '/oauth/token';

        $response = $clientHttp->post($url, [
            'headers'     => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $oauthClient->id,
                'client_secret' => $oauthClient->secret,
                'scope'         => '*',
            ],
        ]);

        return json_decode($response->getBody());
    }
}