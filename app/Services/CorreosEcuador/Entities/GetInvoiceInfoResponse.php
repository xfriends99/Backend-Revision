<?php

namespace App\Services\CorreosEcuador\Entities;

use App\Services\HttpRequests\AbstractResponse;

class GetInvoiceInfoResponse extends AbstractResponse
{
    /** @var bool */
    private $error;

    /** @var string */
    private $message;

    /** @var string|null */
    private $invoice_number;

    /** @var string|null */
    private $invoice_at;

    /** @var string|null */
    private $invoice_link;

    /** @var string|null */
    private $invoice_credentials;

    public function __construct($message, $error, $invoice_number = null, $invoice_at = null, $invoice_link = null, $invoice_credentials = null)
    {
        $this->message = $message;
        $this->error = $error;
        $this->invoice_number = $invoice_number;
        $this->invoice_at = $invoice_at;
        $this->invoice_link = $invoice_link;
        $this->invoice_credentials = $invoice_credentials;
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
     * @return string|null
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * @return string|null
     */
    public function getInvoiceAt()
    {
        return $this->invoice_at;
    }

    /**
     * @return string|null
     */
    public function getInvoiceLink()
    {
        return $this->invoice_link;
    }

    /**
     * @return string|null
     */
    public function getInvoiceCredentials()
    {
        return $this->invoice_credentials;
    }

}