<?php

namespace App\Services\Mailamericas\Shipping;

use App\Models\Purchase;
use App\Models\WorkOrder;
use App\Services\Packages\Entity\Shipment;
use Carbon\Carbon;
use Exception;
use FPDI;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;

class LabelingService
{
    /**
     * @param Shipment $shipment
     * @return boolean
     * @throws Exception
     */
    public function label(Shipment &$shipment)
    {
        if (!$response = $this->request($shipment)) {
            return null;
        }

        return self::parseResponse($response, $shipment);
    }

    /**
     * @param WorkOrder $workOrder
     * @param string $tracking
     *
     * @return string
     */
    public function drawConsolidationLabel(WorkOrder $workOrder, $tracking)
    {
        $pdf = new FPDI('P', 'in');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(0, 0, 0);
        $pdf->setAutoPageBreak(true, 0);
        $pdf->AddPage('P', [4, 4]);

        $pdf->SetFontSize(18);
        $pdf->MultiCell(3.8, 0.12, 'FOR  CONSOLIDATION', 1, 'C', false, 1, 0.09, 0.1);

        $pdf->SetFontSize(12);
        $pdf->MultiCell(3.8, 0.12, "Work Order ID: {$workOrder->id}", 0, 'L', false, 1, 0.09, 0.8);

        $barcode_style = [
            'position'     => '',
            'align'        => 'C',
            'stretch'      => false,
            'fitwidth'     => false,
            'cellfitalign' => '',
            'border'       => false,
            'hpadding'     => '0',
            'vpadding'     => 'auto',
            'fgcolor'      => array(0, 0, 0),
            'bgcolor'      => false,
            'text'         => false,
            'font'         => 'helvetica',
            'fontsize'     => 12,
            'stretchtext'  => 4
        ];

        $pdf->write1DBarcode($workOrder->id, 'C128', 0.09, 1.1, 2.25, 0.55, '', $barcode_style, 'N');

        $pdf->MultiCell(3.8, 0.2, "Tracking: {$tracking}", 0, 'L', false, 1, 0.09, 2);

        /** @var Purchase $purchase */
        $purchase = $workOrder->purchases->first();

        $locker_code = $purchase->getUserLockerCode();
        $pdf->MultiCell(3.8, 0.2, "Locker: {$locker_code}", 0, 'L', false, 1, 0.09, 2.4);

        // Processed vs Total
        $total = $workOrder->getPurchasesCount();
        $processed = $workOrder->purchases->filter(function (Purchase $purchase) {
            return $purchase->isProcessed();
        })->count();

        $pdf->MultiCell(3.8, 0.2, "{$processed} / {$total}", 0, 'L', false, 1, 0.09, 2.8);

        return 'data:image/pdf;base64,' . base64_encode($pdf->Output('none', 'S'));
    }

    /**
     * @param Shipment $shipment
     * @return mixed
     * @throws Exception
     */
    private function request(Shipment $shipment)
    {
        try {
            // Call API
            $client = new Client([
                RequestOptions::HTTP_ERRORS => false
            ]);
            $response = $client->get(env('SHIPPING_API_URL') . '/v1/labels/' . $shipment->getTracking() . '?access_token=' . env('SHIPPING_API_ACCESS_TOKEN'));

            // Log response
            $response = $response->getBody()->getContents();
            logger('[Shipping] Response');
            logger($response);

            return json_decode($response);
        } catch (ClientException $e) {
            throw new Exception($e->getResponse()->getBody()->getContents(), $e->getResponse()->getStatusCode());
        }
    }

    /**
     * @param $response
     * @param Shipment $shipment
     * @return boolean
     * @throws Exception
     */
    private function parseResponse($response, Shipment &$shipment)
    {
        if (isset($response->error) && $response->error) {
            throw new Exception($response->message);
        }

        if (!isset($response->data)) {
            throw new Exception('Missing data field in response.');
        }

        $shipment->setTracking($response->data->tracking);
        $shipment->setLabel($response->data->label);

        return true;
    }
}
