<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Passport\AccessTokenService;
use Illuminate\Http\Request;
use App\Traits\JsonApiResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use JsonApiResponse;

    /** @var AccessTokenService $accessTokenService */
    protected $accessTokenService;

    /**
     * AuthController constructor.
     * @param AccessTokenService $accessTokenService
     */
    public function __construct(AccessTokenService $accessTokenService)
    {
        $this->accessTokenService = $accessTokenService;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'        => 'required|string|email',
            'password' => 'required|string',
        ]);

        $data = array_merge($request->only('email', 'password'),
            [
                'platform_id' => current_platform()->id
            ]);

        if ($this->attemptLogin($data, $request->get('remember', true))) {
            /** @var User $user */
            $user = Auth::user();

            if(!$user->isCorrectPlatform()) {
                return self::errorResponse('Las credenciales son incorrectas.', 400);
            }

            if(!$user->isActive()){
                return self::errorResponse('El usuario no ha sido verificado', 400);
            }
            /** @var string $access_token */
            $access_token = $this->accessTokenService->createFromUser($user);

            Auth::guard()->login($user);

            $request->session()->regenerate();

            return self::success(['access_token' => $access_token]);
        }

        return self::errorResponse('Las credenciales son incorrectas.', 400);

    }

    /**
     * Attempt to log the user into the application.
     *
     * @param array $params
     * @param $remember
     * @return mixed
     */
    protected function attemptLogin(array $params, $remember)
    {
        return Auth::guard()->attempt(
            $params, $remember
        );
    }
}