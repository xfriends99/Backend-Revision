<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 15/3/2019
 * Time: 3:46 PM
 */

namespace App\Repositories;


use App\Models\CardBrand;

class CardBrandRepository extends AbstractRepository
{
    /**
     * CardBrandRepository constructor.
     * @param CardBrand $model
     */
    function __construct(CardBrand $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('card_brands.*');

        if (isset($filters['type']) && $filters['type']) {
            $query = $query->ofType($filters['type']);
        }

        return $query;
    }
    
    public function getByType($type)
    {
        return $this->filter(['type' => $type])->first();
    }
}