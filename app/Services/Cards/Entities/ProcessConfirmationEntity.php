<?php

namespace App\Services\Cards\Entities;


class ProcessConfirmationEntity
{
    /**
     * @param array $attributes
     */
    public function setParams(array $attributes)
    {
        foreach ($attributes as $key => $val){
            if(property_exists($this, $key) && $val){
                $this->{$key} = $val;
            }
        }
    }
}