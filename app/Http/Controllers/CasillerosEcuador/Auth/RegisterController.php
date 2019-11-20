<?php

namespace App\Http\Controllers\CasillerosEcuador\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\IdentificationType;
use App\Models\User;
use App\Repositories\CountryRepository;
use App\Repositories\IdentificationTypeRepository;
use App\Services\CorreosEcuador\Entities\GetUserInfoResponse;
use App\Services\CorreosEcuador\Requests\GetUserInfoRequestService;
use App\Services\Users\RegistrationService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/email/verify';

    /** @var CountryRepository */
    protected $countryRepository;

    /** @var RegistrationService */
    protected $registrationService;

    /** @var IdentificationTypeRepository */
    protected $identificationTypeRepository;

    /**
     * RegisterController constructor.
     * @param CountryRepository $countryRepository
     * @param RegistrationService $registrationService
     * @param IdentificationTypeRepository $identificationTypeRepository
     */
    public function __construct(
        CountryRepository $countryRepository,
        RegistrationService $registrationService,
        IdentificationTypeRepository $identificationTypeRepository
    ) {
        $this->middleware('guest');

        $this->countryRepository = $countryRepository;
        $this->registrationService = $registrationService;
        $this->identificationTypeRepository = $identificationTypeRepository;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {   
        $request = request();
        $token = $request->get('ref');
        return view('casillerosecuador.first_step', compact('token'));
    }

    public function firstStep(Request $request)
    {
        $this->validate($request, [
            'born_at'        => 'required|date_format:d/m/Y',
            'identification' => 'required|numeric',
        ]);

        $born_at = $request->get('born_at');
        $identification = $request->get('identification');

        /** @var GetUserInfoRequestService $getUserInfoRequestService */
        $getUserInfoRequestService = new GetUserInfoRequestService($identification);

        try {
            /** @var GetUserInfoResponse $getUserInfoResponse */
            $getUserInfoResponse = $getUserInfoRequestService->request();
        } catch (Exception $e) {
            logger($e->getMessage());

            return redirect()->back()->withInput()->withErrors('Ocurrió un error verificando sus datos, por favor intente nuevamente.');
        }

        // Check if valid response
        if ($getUserInfoResponse->hasErrors() or empty($getUserInfoResponse->getCedula())) {
            return redirect()->back()->withInput()->withErrors('No esta registrado como persona natural de Ecuador');
        }

        // Validaciones de fecha según tipo de usuarioAvisalo
        $avisalo = $getUserInfoResponse->getUsuarioAvisalo();

        if ($avisalo === 0 or $avisalo === 1) {
            if (!$getUserInfoResponse->isFechaNacimientoEqualTo($getUserInfoResponse->parseBornAtFromCalendar($born_at))) {
                return redirect()->back()->withInput()->withErrors('Fecha de nacimiento inválida.');
            }
        }

        // Revisar usuario previamente registrado
        if ($this->registrationService->userExistsByIdentification($identification)) {
            return redirect()->back()->withInput()->withErrors('Usuario ya existente en "Club Correos"');
        }

        return view('casillerosecuador.register', compact('identification', 'getUserInfoResponse'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws Exception
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        /** @var GetUserInfoRequestService $getUserInfoRequestService */
        $getUserInfoRequestService = new GetUserInfoRequestService($request->get('identification'));

        try {
            /** @var GetUserInfoResponse $getUserInfoResponse */
            $getUserInfoResponse = $getUserInfoRequestService->request();
            $identification = $request->get('identification');
        } catch (Exception $e) {
            logger($e->getMessage());

            throw new Exception('Ocurrió un error verificando sus datos, por favor intente nuevamente.');
        }

        if ($validator->fails()) {
            session()->flashInput($request->input());

            return view('casillerosecuador.register', compact('identification', 'getUserInfoResponse'))->withErrors($validator->errors());
        }

        // Update email specified on the form
        $user_email = strtolower($request->get('email', $getUserInfoResponse->getMailPersonal()));
        $getUserInfoResponse->setMailPersonal($user_email);

        // Check if user already exists
        if ($this->registrationService->userExistsByEmail($user_email)) {
            session()->flashInput($request->input());
            $errors = new MessageBag(['El usuario ya está registrado en "Club Correos" bajo este mismo correo electrónico.']);

            return view('casillerosecuador.register', compact('identification', 'getUserInfoResponse'))->withErrors($errors);
        }

        try {
            // Finally, create user and call EC WebService
            $user = $this->create($request->all(), $getUserInfoResponse);

            // Trigger registered event
            event(new Registered($user));

            // Authenticate user
            $this->guard()->login($user);

            // Redirect to user Home
            return redirect($this->redirectPath());
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            session()->flashInput($request->input());
            $errors = new MessageBag(['No se pudo completar el registro.']);

            return view('casillerosecuador.register', compact('identification', 'getUserInfoResponse'))->withErrors($errors);
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name'         => ['required', 'string', 'max:255'],
            'last_name'          => ['required', 'string', 'max:255'],
            'first_name2'        => ['string', 'max:255'],
            'last_name2'         => ['string', 'max:255'],
            'email'              => ['email', 'max:255'],
            'confirm_email'      => ['email', 'max:255'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'phone'              => ['required', 'string', 'max:255'],
            'phone_conventional' => ['required', 'string', 'max:255'],
            'identification'     => ['required', 'string'],
            'township'           => ['required'],
            'agreement'          => ['required'],
            'referrer_token'         => ['sometimes', 'exists:users,email']
        ]);
    }

    /**
     * @param array $data
     * @param GetUserInfoResponse $getUserInfoResponse
     * @return User
     * @throws Exception
     */
    protected function create(array $data, GetUserInfoResponse $getUserInfoResponse)
    {
        /** @var Country $country */
        $country = $this->countryRepository->getByCode('EC');

        /** @var User $user */
        $refererUser = null;
        if (!empty($data['referrer_token'])) {
            $refererUser = $this->registrationService->userByEmail($data['referrer_token']);
        }

        /** @var IdentificationType $identificationType */
        $identificationType = $this->identificationTypeRepository->getByCountryAndKey($country, 'ci');

        $first1 = $data['first_name'];
        $first2 = isset($data['first_name2']) ? $data['first_name2'] : '';
        $last1 = $data['last_name'];
        $last2 = isset($data['last_name2']) ? $data['last_name2'] : '';

        /** @var User $user */
        $user = $this->registrationService->registerFromEcuador(
            $getUserInfoResponse,
            $country,
            $getUserInfoResponse->getMailPersonal(),
            $first1,
            $first2,
            $last1,
            $last2,
            $data['phone'],
            $data['phone_conventional'],
            $getUserInfoResponse->getCedula(),
            $getUserInfoResponse->getFechaNacimientoAsCarbon(),
            $identificationType,
            $data['password']
        );

        return $user;
    }
}
