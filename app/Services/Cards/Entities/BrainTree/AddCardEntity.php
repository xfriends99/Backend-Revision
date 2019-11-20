<?php

namespace App\Services\Cards\Entities\BrainTree;

use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use Braintree\Customer;

class AddCardEntity extends BaseAddCardEntity
{
    /** @var string */
    protected $cardHolderName;

    /** @var string */
    protected $token;

    /** @var Customer */
    protected $customer;

    /**
     * @return string
     */
    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

}