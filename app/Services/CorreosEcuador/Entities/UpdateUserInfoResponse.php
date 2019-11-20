<?php

namespace App\Services\CorreosEcuador\Entities;

use App\Services\HttpRequests\AbstractResponse;

class UpdateUserInfoResponse extends AbstractResponse
{
    /** @var string */
    private $message;

    /** @var bool */
    private $error;

    /** @var int|null */
    private $id;

    /** @var int|null */
    private $state;

    public function __construct($message, $error, $id = null, $state = 0)
    {
        $this->message = $message;
        $this->error = $error;
        $this->id = $id;
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->error;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getState()
    {
        return $this->state;
    }
}