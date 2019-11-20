<?php

namespace App\Http\Controllers\Api\Transformers;

use App\Models\Card;
use League\Fractal\TransformerAbstract;

class CardTransformer extends TransformerAbstract
{
    /**
     * @param Card $card
     * @return array
     */
    public function transform(Card $card)
    {
        return [
            'id'            => $card->id,
            'name'         => $card->name,
            'number'        => $card->number,
            'created_at'    => $card->created_at->format('d/m/Y'),
            'cardBrand'         => [
                'brand' => $card->getCardBrandName()
            ],
            'default' => $card->default
        ];
    }
}