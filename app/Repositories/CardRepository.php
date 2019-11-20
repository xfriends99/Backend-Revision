<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 15/3/2019
 * Time: 3:43 PM
 */

namespace App\Repositories;


use App\Models\Card;

class CardRepository extends AbstractRepository
{
    /**
     * CardRepository constructor.
     * @param Card $model
     */
    function __construct(Card $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('cards.*');

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->ofUserId($filters['user_id']);
        }

        return $query;
    }

    /**
     * @param array $attributes
     * @param array $filters
     * @return bool
     */
    public function updateMultiple(array $attributes, array $filters = [])
    {
        $query = $this->model;

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->ofUserId($filters['user_id']);
        }

        return $query->update($attributes);
    }

    /**
     * @param Card $card
     * @return bool
     */
    public function markAsDefault(Card $card)
    {
        return $this->update($card, ['default' => true]);
    }

}
