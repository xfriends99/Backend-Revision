<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\Platform;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SiteRepository
 * @package App\Repositories
 */
class SiteRepository extends AbstractRepository
{
    /**
     * SiteRepository constructor.
     * @param Site $model
     */
    function __construct(Site $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function filter(array $filters = [])
    {
        /** @var Builder $query */
        $query = $this->model->query();

        $query = $query->select('sites.*');

        if (isset($filters['country_id']) && $filters['country_id']) {
            $query = $query->ofCountryId($filters['country_id']);
        }

        if (isset($filters['platform_id']) && $filters['platform_id']) {
            $query = $query->ofPlatformId($filters['platform_id']);
        }

        if (isset($filters['default']) && $filters['default']) {
            $query = $query->ofDefault();
        }

        return $query;
    }

    public function getByPlatformAndCountry(Platform $platform, Country $country)
    {
        return $this->filter([
            'platform_id' => $platform->id,
            'country_id' => $country->id
        ])->first();
    }

    public function getPlatformDefault(Platform $platform)
    {
        return $this->filter(['platform_id' => $platform->id, 'default' => true])->first();
    }
}
