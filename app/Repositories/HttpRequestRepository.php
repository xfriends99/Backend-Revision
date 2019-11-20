<?php

namespace App\Repositories;

use App\Models\HttpRequest;

/**
 * Class HttpRequestRepository
 * @package App\Repositories
 */
class HttpRequestRepository extends AbstractRepository
{

    /**
     * HttpRequestRepository constructor.
     * @param HttpRequest $model
     */
    function __construct(HttpRequest $model)
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
        $query = $this->model->select('http_requests.*');

        return $query;
    }
}