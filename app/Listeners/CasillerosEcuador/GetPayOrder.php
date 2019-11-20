<?php

namespace App\Listeners\CasillerosEcuador;

use App\Events\PackageGetInvoice;
use App\Listeners\PlatformListener;
use App\Models\Invoice;
use App\Models\Package;
use App\Repositories\InvoiceRepository;
use App\Services\CorreosEcuador\Entities\GetInvoiceInfoResponse;
use App\Services\CorreosEcuador\Requests\GetPayOrderRequestService;
use Exception;

class GetPayOrder extends PlatformListener
{
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 900;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 3600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /** @var InvoiceRepository $invoiceRepository */
    protected $invoiceRepository;

    /**
     * Create the event listener.
     *
     * @param InvoiceRepository $invoiceRepository
     * @return void
     */
    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**'
     * @param PackageGetInvoice $event
     * @throws Exception
     */
    public function handle(PackageGetInvoice $event)
    {
        if (!$this->isValidPlatform($event)) {
            return;
        }

        /** @var Package $package */
        $package = $event->package;

        /** @var Invoice $invoice */
        $invoice = $package->invoice;

        /** @var GetPayOrderRequestService $getPayOrderRequestService */
        $getPayOrderRequestService = new GetPayOrderRequestService($package);

        /** @var GetInvoiceInfoResponse $response */
        $response = $getPayOrderRequestService->request();

        if ($response->hasErrors()) {
            throw new Exception('Error obteniendo orden de pago');
        }

        $this->invoiceRepository->update($invoice, ['external_invoice_link' => $response->getInvoiceLink()]);
    }
}
