<?php

namespace App\Repositories;

use App\Models\PackagePrealert;

/**
 * Class PackagePrealertRepository
 * @package App\Repositories
 */
class PackagePrealertRepository extends AbstractRepository
{

    /**
     * HttpRequestRepository constructor.
     * @param PackagePrealert $model
     */
    function __construct(PackagePrealert $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     *
     * @return mixed
     */
    public function filter(array $filters = [])
    {
        $query = $this->model->select('package_prealerts.*');

        return $query;
    }
}