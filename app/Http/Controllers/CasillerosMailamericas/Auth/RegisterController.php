<?php

namespace App\Http\Controllers\CasillerosMailamericas\Auth;

use App\Events\UserOptedIntoNewsletter;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\IdentificationType;
use App\Models\User;
use App\Repositories\CountryRepository;
use App\Repositories\IdentificationTypeRepository;
use App\Services\Users\RegistrationService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

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
        $countries = $this->countryRepository->filter(['tenant' => true])->get();
        $identificationTypes = collect();

        if ($country_id = old('country_id')) {
            $identificationTypes = $this->identificationTypeRepository->filter(compact('country_id'))->get();
        }

        return view('casillerosmailamericas.register', compact('countries', 'identificationTypes'));
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
            'first_name'             => ['required', 'string', 'max:255'],
            'last_name'              => ['required', 'string', 'max:255'],
            'email'                  => ['required', 'email', 'max:255'],
            'password'               => ['required', 'string', 'min:8', 'confirmed'],
            'phone'                  => ['required', 'string', 'max:255'],
            'identification_type_id' => ['required', 'exists:identification_types,id'],
            'identification'         => ['required', 'string'],
            'born_at'                => ['required', 'date_format:d/m/Y'],
            'country_id'             => ['required', 'exists:countries,id'],
            'agreement'              => ['required'],
            'referrer_token'         => ['sometimes', 'exists:users,email']
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        // Check if user already exists
        if ($this->registrationService->userExistsByEmail($request->get('email'))) {
            return redirect()->back()->withErrors('El usuario ya se encuentra registrado, intente iniciar sesión');
        }

        if ($user = $this->create($request->all())) {
            event(new Registered($user));

            // Subscribe to newsletter
            if ($request->filled('newsletter')) {
                event(new UserOptedIntoNewsletter($user));
            }

            $this->guard()->login($user);
        } else {
            return redirect()->back()->withErrors('No se pudo completar el registro');
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * @param array $data
     * @return User|\Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function create(array $data)
    {
        /** @var Country $country */
        $country = $this->countryRepository->getById($data['country_id']);

        /** @var User $user */
        $refererUser = null;
        if (!empty($data['referrer_token'])) {
            $refererUser = $this->registrationService->userByEmail($data['referrer_token']);
        }
        
        /** @var IdentificationType $identificationType */
        $identificationType = null;
        if (!empty($data['identification_type_id'])) {
            $identificationType = $this->identificationTypeRepository->getById($data['identification_type_id']);
        }

        return $this->registrationService->register($country,
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            null,
            $data['identification'],
            Carbon::createFromFormat('d/m/Y', $data['born_at']),
            $identificationType,
            $refererUser,
            $data['password']
        );
    }

    public function getIdentificationTypes(Request $request)
    {   
        if ($request->expectsJson()) {
            if (!$country_id = $request->get('country_id')) {
                return response()->json([
                    'data' => [],
                    'meta' => [
                        'error'   => true,
                        'message' => 'Invalid country.',
                        'code'    => 400,
                    ],
                ], 400, [], JSON_PRETTY_PRINT);
            }

            /** @var IdentificationTypeRepository $identificationTypeRepository */
            $identificationTypeRepository = app(IdentificationTypeRepository::class);

            /** @var Collection $identificationTypes */
            $identificationTypes = $identificationTypeRepository->filter(['country_id' => $country_id])->get();

            return response()->json([
                'data' => $identificationTypes->toArray(),
                'meta' => [
                    'error'   => false,
                    'message' => 'OK',
                    'code'    => 200,
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        }
    }
}
