<?php

namespace App\Services\Users;

use App\Models\Country;
use App\Models\IdentificationType;
use App\Models\Platform;
use App\Models\State;
use App\Models\Timezone;
use App\Models\User;
use App\Repositories\IdentificationTypeRepository;
use App\Repositories\LockerRepository;
use App\Repositories\StateRepository;
use App\Repositories\TimezoneRepository;
use App\Repositories\UserRepository;
use App\Services\Addresses\CreateService as CreateServiceAddress;
use App\Services\CorreosEcuador\Entities\GetUserInfoResponse as UserEntity;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\CorreosEcuador\Requests\UpdateUserRequestService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationService
{
    /** @var IdentificationTypeRepository */
    protected $identificationTypeRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var LockerRepository */
    protected $lockerRepository;

    /** @var TimezoneRepository */
    protected $timezoneRepository;

    /** @var StateRepository */
    protected $stateRepository;

    /** @var CreateServiceAddress */
    protected $createServiceAddress;

    /**
     * RegistrationService constructor.
     * @param IdentificationTypeRepository $identificationTypeRepository
     * @param UserRepository $userRepository
     * @param LockerRepository $lockerRepository
     * @param TimezoneRepository $timezoneRepository
     * @param StateRepository $stateRepository
     * @param CreateServiceAddress $createServiceAddress
     */
    public function __construct(
        IdentificationTypeRepository $identificationTypeRepository,
        UserRepository $userRepository,
        LockerRepository $lockerRepository,
        TimezoneRepository $timezoneRepository,
        StateRepository $stateRepository,
        CreateServiceAddress $createServiceAddress
    ) {
        $this->identificationTypeRepository = $identificationTypeRepository;
        $this->userRepository = $userRepository;
        $this->lockerRepository = $lockerRepository;
        $this->timezoneRepository = $timezoneRepository;
        $this->stateRepository = $stateRepository;
        $this->createServiceAddress = $createServiceAddress;
    }

    /**
     * @param Country $country
     * @param $email
     * @param $first_name
     * @param $last_name
     * @param $phone
     * @param $phone2
     * @param $identification
     * @param Carbon $bornAt
     * @param IdentificationType|null $identificationType
     * @param User|null $refererUser
     * @param null $password
     * @return User
     * @throws Exception
     */
    public function register(Country $country, $email, $first_name, $last_name, $phone, $phone2, $identification, Carbon $bornAt, IdentificationType $identificationType = null, User $refererUser = null, $password = null)
    {
        return $this->createUser($country, $email, $first_name, $last_name, $phone, $phone2, $identification, $bornAt, $identificationType, $refererUser, $password);
    }

    /**
     * @param UserEntity $userEntity
     * @param Country $country
     * @param $email
     * @param $first1_name
     * @param $first2_name
     * @param $last1_name
     * @param $last2_name
     * @param $phone
     * @param $phone2
     * @param $identification
     * @param Carbon $bornAt
     * @param IdentificationType|null $identificationType
     * @param User|null $refererUser
     * @param null $password
     * @return User
     * @throws Exception
     */
    public function registerFromEcuador(
        UserEntity $userEntity,
        Country $country,
        $email,
        $first1_name,
        $first2_name,
        $last1_name,
        $last2_name,
        $phone,
        $phone2,
        $identification,
        Carbon $bornAt,
        IdentificationType $identificationType = null,
        User $refererUser = null,
        $password = null
    ) {
        /** @var State $state */
        $state = $this->stateRepository->getByNameAndCountry($country, request()->get('state'));

        // First name
        $first_name = $first1_name;
        if (!empty($first2_name)) {
            $first_name .= ' ' . $first2_name;
        }

        // Last name
        $last_name = $last1_name;
        if (!empty($last2_name)) {
            $last_name .= ' ' . $last2_name;
        }

        /** @var User $user */
        $user = $this->createUser($country, $email, $first_name, $last_name, $phone, $phone2, $identification, $bornAt,
            $identificationType, $refererUser, $password, $userEntity->getIdPersona());

        /* Update user data in ecuador service  */
        if (!$this->updateUserInEcuadorSystem($user, request())) {
            throw new Exception('Error updating info in Correos Ecuador.');
        }

        $this->createServiceAddress->create($user, $country, $state, request()->get('address1'),
            request()->get('town'), request()->get('township'), request()->get('postal_code'), null, null,
            request()->get('address2'), request()->get('number'), request()->get('reference'));

        return $user;
    }

    /**
     * @param User $user
     * @param $request
     * @return bool
     * @throws Exception
     */
    private function updateUserInEcuadorSystem(User $user, $request)
    {
        try {
            $updateUserRequestService = new UpdateUserRequestService($user, $request);

            /** @var UpdateUserInfoResponse $response */
            $response = $updateUserRequestService->request();
            if ($response->hasErrors()) {
                logger($response->getErrors());
                throw new Exception('Request with errors');
            }
            
            // Update user external id
            $this->userRepository->update($user, ['external_id' => $response->getId()]);

            return true;
        } catch (Exception $e) {
            logger($e->getMessage());
            // Delete created locker and user
            $this->lockerRepository->delete($user->locker);
            $this->userRepository->delete($user);

            throw new Exception;
        }
    }

    /**
     * @param Country $country
     * @param $email
     * @param $first_name
     * @param $last_name
     * @param $phone
     * @param $phone2
     * @param $identification
     * @param Carbon $bornAt
     * @param IdentificationType|null $identificationType
     * @param User|null $refererUser
     * @param null $password
     * @param null $external_id
     * @return User|bool
     * @throws Exception
     */
    private function createUser(
        Country $country,
        $email,
        $first_name,
        $last_name,
        $phone,
        $phone2,
        $identification,
        Carbon $bornAt,
        IdentificationType $identificationType = null,
        User $refererUser = null,
        $password = null,
        $external_id = null
    ) {
        if (!$identificationType) {
            $identificationType = $this->identificationTypeRepository->getByCountryAndKey($country, 'dni');
        }

        if (!$password) {
            $hashed_password = Hash::make(Str::random('8'));
        } else {
            $hashed_password = Hash::make($password);
        }

        /** @var Timezone $timezone */
        $timezone = self::detectTimezone($country);

        /** @var Platform $platform */
        $platform = current_platform();

        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = $this->userRepository->create([
                'country_id'             => $country->id,
                'timezone_id'            => $timezone ? $timezone->id : null,
                'platform_id'            => $platform->id,
                'external_id'            => $external_id,
                'email'                  => $email,
                'first_name'             => $first_name,
                'last_name'              => $last_name,
                'phone'                  => $phone,
                'phone2'                 => $phone2,
                'identification'         => $identification,
                'identification_type_id' => $identificationType ? $identificationType->id : null,
                'born_at'                => $bornAt->toDateString(),
                'password'               => $hashed_password,
                'referrer_id'            => $refererUser ? $refererUser->id : null
            ]);

            $locker_code = $this->generateLockerCode($country);

            $this->createLocker($user, $locker_code);

            DB::commit();
        } catch (Exception $e) {
            logger($e->getMessage());

            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return $user;
    }

    public function userExistsByEmail($email)
    {
        /** @var int $platform_id */
        $platform_id = current_platform()->id;

        $user = $this->userRepository->filter(compact('email', 'platform_id'))->first();

        return !empty($user);
    }

    public function userByEmail($email)
    {
        /** @var int $platform_id */
        $platform_id = current_platform()->id;

        $user = $this->userRepository->filter(compact('email', 'platform_id'))->first();

        return $user;
    }

    public function userExistsByIdentification($identification)
    {
        /** @var int $platform_id */
        $platform_id = current_platform()->id;

        $user = $this->userRepository->filter(compact('identification', 'platform_id'))->first();

        return !empty($user);
    }

    private function generateLockerCode(Country $country)
    {
        $code = null;
        $available = false;
        while (!$available) {
            $code = "PB" . substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 50)), 0, 5);
            $locker = $this->lockerRepository->getByCode($code);
            if (!$locker) {
                $available = true;
            }
        }

        return $code;
    }

    private function createLocker(User $user, $code)
    {
        return $this->lockerRepository->create([
            'code'    => $code,
            'user_id' => $user->id
        ]);
    }

    private function detectTimezone(Country $country)
    {
        switch ($country->code) {
            case 'AR':
                return $this->timezoneRepository->getByNameAndDescription('America/Argentina/Buenos_Aires', '(UTC-03:00) Buenos Aires');
            case 'BR':
                return $this->timezoneRepository->getByName('America/Sao_Paulo');
            case 'EC':
                return $this->timezoneRepository->getByNameAndDescription('America/Bogota', '(UTC-05:00) Quito');
            case 'CO':
                return $this->timezoneRepository->getByNameAndDescription('America/Bogota', '(UTC-05:00) Bogota');
            case 'CL':
                return $this->timezoneRepository->getByName('America/Santiago');
            case 'PE':
                return $this->timezoneRepository->getByName('America/Lima');
        }

        return $this->timezoneRepository->getByName('UTC');
    }
}