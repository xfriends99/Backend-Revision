<?php

namespace App\Services\Tariffs;

use App\Models\Country;
use App\Models\Platform;
use App\Services\Tariffs\Exception\InvalidZipCodeException;
use Exception;

class CalculatorService
{
    /** @var TariffRateQuoteService */

    protected $tariffRateQuoteService;

    public function __construct(TariffRateQuoteService $tariffRateQuoteService)
    {
        $this->tariffRateQuoteService = $tariffRateQuoteService;
    }

    /**
     * @param Platform $platform
     * @param Country $country
     * @param string $admin_level_1
     * @param string $admin_level_2
     * @param string $admin_level_3
     * @param string $zip_code
     * @param string $service
     * @param float $weight
     * @param int $items
     * @return float
     * @throws Exception
     */
    public function quote(Platform $platform, Country $country, string $admin_level_1, string $admin_level_2, string $admin_level_3, string $zip_code, string $service, float $weight, int $items)
    {
        if ($platform->isCorreosEcuador()) {
            return $this->quoteCasillerosEcuador($admin_level_1, $admin_level_2, $admin_level_3, $zip_code, $service, $weight, $items);
        } elseif ($platform->isMailamericas()) {
            return $this->quoteCasillerosMailamericas($country->code, $zip_code, $service, $weight, $items);
        }

        throw new Exception('Platform not supported');
    }

    /**
     * @param string $admin_level_1
     * @param string $admin_level_2
     * @param string $admin_level_3
     * @param string $zip_code
     * @param string $service
     * @param float $weight
     * @param int $items
     * @return float
     * @throws InvalidZipCodeException|Exception
     */
    public function quoteCasillerosEcuador(string $admin_level_1, string $admin_level_2, string $admin_level_3, string $zip_code, string $service, float $weight, int $items)
    {
        $quote = $this->tariffRateQuoteService->quote($weight, 'EC', $admin_level_1, $admin_level_2, $admin_level_3, $zip_code, $service);

        return $quote;
    }

    /**
     * @param string $country_code
     * @param string $zip_code
     * @param string $service
     * @param float $weight
     * @param int $items
     * @return float
     * @throws InvalidZipCodeException|Exception
     */
    public function quoteCasillerosMailamericas(string $country_code, string $zip_code, string $service, float $weight, int $items)
    {
        if ($country_code == 'AR') {
            $zip_code = '1000';
        } elseif ($country_code == 'PE') {
            $zip_code = '150101';
        } elseif ($country_code == 'CL') {
            $zip_code = '8320000';
        } elseif ($country_code == 'MX') {
            $zip_code = '15620';
        }

        $quote = $this->tariffRateQuoteService->quote($weight, $country_code, '', '', '', $zip_code, $service);

        return $quote;
    }
}