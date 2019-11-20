<?php
namespace App\Repositories;

use App\Models\WeightUnit;
use Illuminate\Database\Eloquent\Builder;

class WeightUnitRepository extends AbstractRepository
{
    /**
     * PackageRepository constructor.
     * @param WeightUnit $model
     */
    public function __construct(WeightUnit $model)
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

        $query = $query->select('weight_units.*');

        if (isset($filters['code']) && $filters['code']) {
            $query = $query->ofCode($filters['code']);
        }

        return $query->orderBy('name', 'asc');
    }
}
