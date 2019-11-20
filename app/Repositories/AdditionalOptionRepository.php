<?php

namespace App\Repositories;

use App\Models\AdditionalOption;

/**
 * Class AdditionalOptionRepository
 * @package App\Repositories
 */
class AdditionalOptionRepository extends AbstractRepository
{
    /**
     * AdditionalOptionRepository constructor.
     * @param AdditionalOption $model
     */
    function __construct(AdditionalOption $model)
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
        $query = $this->model->select('additional_options.*');

        return $query;
    }

}