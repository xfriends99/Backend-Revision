<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use App\Repositories\CountryRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\WarehouseRepository;
use App\Services\Cloud\MultiAnalyticsFactory;
use App\Services\Packages\PackageService;
use App\Services\Services\Exception\ServiceValidationException;
use App\Services\Services\ServiceFactory;
use App\Services\Services\ValidationEntity;
use App\Services\Tariffs\CalculatorService;
use App\Services\Tariffs\Exception\InvalidZipCodeException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalculatorController extends Controller
{
    /** @var  CountryRepository */
    protected $countryRepository;

    /** @var  ServiceTypeRepository */
    protected $serviceTypeRepository;

    /** @var WarehouseRepository */
    protected $warehouseRepository;

    /** @var ServiceFactory */
    protected $serviceFactory;

    /** @var CalculatorService */
    protected $calculatorService;

    /**
     * CalculatorController constructor.
     * @param CountryRepository $countryRepository
     * @param ServiceTypeRepository $serviceTypeRepository
     * @param ServiceFactory $serviceFactory
     * @param CalculatorService $calculatorService
     */
    public function __construct(
        CountryRepository $countryRepository,
        ServiceTypeRepository $serviceTypeRepository,
        WarehouseRepository $warehouseRepository,
        ServiceFactory $serviceFactory,
        CalculatorService $calculatorService
    ) {
        $this->countryRepository = $countryRepository;
        $this->serviceTypeRepository = $serviceTypeRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->serviceFactory = $serviceFactory;
        $this->calculatorService = $calculatorService;
    }

    public function index(Request $request)
    {
        if (current_platform()->isMailamericas()) {
            return $this->showFormCasillerosMailamericas($request);
        } elseif (current_platform()->isCorreosEcuador()) {
            return $this->showFormCasillerosEcuador($request);
        }

        abort(404);
    }

    private function showFormCasillerosMailamericas(Request $request, $tariff = null)
    {
        $params = $request->all();
        $layout = current_platform()->key;

        // Origin Country
        // @TODO: move to view composer
        if (old('origin_country_id')) {
            $originCountry = $this->countryRepository->getById(old('origin_country_id'));
        } else {
            $originCountry = $this->countryRepository->getByCode('US');
        }
        $warehouses = $this->warehouseRepository->all();

        // Destination Country
        // @TODO: move to view composer
        /** @var Country $destinationCountry */
        $destinationCountry = null;
        if (old('destination_country_id')) {
            $destinationCountry = $this->countryRepository->getById(old('destination_country_id'));
        } else {
            $destinationCountry = $this->countryRepository->getByCode(\LaravelLocalization::getCurrentLocaleRegional());
        }
        $countries = $this->countryRepository->filter(['code' => ['AR', 'CL', 'CO', 'MX', 'PE']])->get();

        $view = view("{$layout}.calculator.form", compact('originCountry', 'destinationCountry', 'warehouses', 'countries', 'params'));
        if ($tariff) {
            $country_name = $destinationCountry->name;

            $view->nest('quote', "{$layout}.calculator.quote", compact('params', 'tariff', 'country_name'));
        }

        return $view;
    }

    private function showFormCasillerosEcuador(Request $request, $tariff = null)
    {
        $params = $request->all();
        $layout = current_platform()->key;

        $country = $this->countryRepository->getByCode('EC');

        $view = view("{$layout}.calculator.form", compact('country', 'params'));
        if ($tariff) {
            $country_name = $country->name;

            $view->nest('quote', "{$layout}.calculator.quote", compact('params', 'tariff', 'country_name'));
        }

        return $view;
    }

    public function quote(Request $request)
    {
        $params = $request->all();
        $layout = current_platform()->key;

        // Detect country
        $originCountry = null;
        if ($request->get('origin_country_id')) {
            /** @var Country $originCountry */
            $originCountry = $this->countryRepository->getById($request->get('origin_country_id'));
        }

        // Detect destination country
        $destinationCountry = null;
        if ($request->get('destination_country_id')) {
            /** @var Country $destinationCountry */
            $destinationCountry = $this->countryRepository->getById($request->get('destination_country_id'));
        }
        $countries = $this->countryRepository->filter(['code' => ['AR', 'CL', 'CO', 'MX', 'PE']])->get();
        $warehouses = $this->warehouseRepository->all();

        //Detect service
        $service = null;
        if ($request->get('service_type_id') && $originCountry && $destinationCountry && $request->get('weight')) {
            /** @var ServiceType $serviceType */
            $serviceType = $this->serviceTypeRepository->getById($request->get('service_type_id'));

            /** @var Service $service */
            $service = $this->serviceFactory->getByServiceTypeAndOriginDestinationCountry($serviceType, $originCountry, $destinationCountry, $request->get('weight'));

            try {
                $this->serviceFactory->validateService($service, new ValidationEntity($request->get('weight'), collect(array_fill(0, $request->get('items'), ''))));
            } catch (ServiceValidationException $e) {
                return redirect()->route('calculator.index')->withInput()->withErrors($e->getMessage());
            }
        }

        /** @var float $tariff */
        $tariff = null;
        try {
            // Track with MixPanel and ActiveCampaign so far
            /** @var User $user */
            if ($user = Auth::user()) {
                MultiAnalyticsFactory::trackUser($user, 'Calculator');
            } else {
                MultiAnalyticsFactory::trackGuest('Calculator');
            }

            // Quote shipment
            $tariff = current_platform()->isCorreosEcuador() ? $this->quoteCasillerosEcuador($request) : $this->quoteCasillerosMailamericas($request, $destinationCountry, $service);
        } catch (InvalidZipCodeException $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return redirect()->route('calculator.index')->withInput()->withErrors('No pudimos encontrar el código postal que informaste.');
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return redirect()->route('calculator.index')->withInput()->withErrors('No se pudo cotizar su envío.');
        }

        $view = view("{$layout}.calculator.form", compact('originCountry', 'destinationCountry', 'warehouses', 'countries', 'params'));
        if ($tariff) {
            $view->nest('quote', "{$layout}.calculator.quote", compact('params', 'tariff', 'service'));
        }

        return $view;
    }

    /**
     * @param Request $request
     * @param Country $country
     * @param Service|null $service
     * @return float
     * @throws \Illuminate\Validation\ValidationException
     * @throws InvalidZipCodeException|Exception
     */
    private function quoteCasillerosMailamericas(Request $request, Country $country, Service $service = null)
    {
        $this->validate($request, [
            'destination_country_id' => 'required|exists:countries,id',
            'zip_code'               => 'required',
            'weight'                 => 'required|numeric|min:0.001|max:50',
            'items'                  => 'required|integer|min:1'
        ]);

        $weight = floatval($request->get('weight'));
        $items = intval($request->get('items'));
        $country_code = $country->code;
        $zip_code = (string)$request->get('zip_code');
        $service = $service ? $service->code : PackageService::getServiceCode($country, current_platform());

        $tariff = $this->calculatorService->quoteCasillerosMailamericas($country_code, $zip_code, $service, $weight, $items);

        return $tariff;
    }

    /**
     * @param Request $request
     * @return float
     * @throws \Illuminate\Validation\ValidationException
     * @throws InvalidZipCodeException|Exception
     */
    private function quoteCasillerosEcuador(Request $request)
    {
        $this->validate($request, [
            'zip_code' => 'required',
            'weight'   => 'required|numeric|min:0.001|max:50',
            'items'    => 'required|integer|min:1'
        ]);

        $tariff = $this->calculatorService->quoteCasillerosEcuador(
            (string)$request->get('state'),
            (string)$request->get('town'),
            (string)$request->get('township'),
            (string)$request->get('zip_code'),
            'USCAS11EX',
            floatval($request->get('weight')),
            intval($request->get('items'))
        );

        return $tariff;
    }
}
