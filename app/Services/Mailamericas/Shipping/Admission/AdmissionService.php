<?php

namespace App\Services\Mailamericas\Shipping;

use App\Models\Address;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\Packages\Entity\Shipment;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;

class AdmissionService
{
    public function __construct()
    {
    }

    /**
     * @param Shipment $shipment
     * @return boolean
     * @throws Exception
     */
    public function ship(Shipment &$shipment)
    {
        if (!$response = $this->request($shipment)) {
            return null;
        }

        self::parseResponse($response, $shipment);

        return true;
    }

    /**
     * @param Shipment $shipment
     * @return mixed
     * @throws Exception
     */
    private function request(Shipment $shipment)
    {
        try {
            // Prepare request Json
            $json = $this->prepareRequest($shipment);

            // Log request
            logger('[Shipping] Request');
            logger($json);

            // Call API
            $client = new Client([
                RequestOptions::HTTP_ERRORS => false,
                'verify' => !app()->isLocal(),
            ]);

            $response = $client->post(env('SHIPPING_API_URL') . '/v1/admission?access_token=' . env('SHIPPING_API_ACCESS_TOKEN'), [
                'json' => $json
            ]);

            // Log response
            $response = $response->getBody()->getContents();
            logger('[Shipping] Response');
            logger($response);

            return json_decode($response);
        } catch (ClientException $e) {
            throw new Exception($e->getResponse()->getBody()->getContents(), $e->getResponse()->getStatusCode());
        }
    }

    private function prepareRequest(Shipment $shipment)
    {
        $items = collect();

        /** @var WorkOrder $workOrder */
        $workOrder = $shipment->getWorkOrder();

        /** @var Purchase $purchase */
        foreach ($workOrder->purchases as $purchase) {
            /** @var PurchaseItem $item */
            foreach ($purchase->purchaseItems as $item) {
                $items->push([
                    'quantity'       => $item->quantity,
                    'description'    => $item->description,
//                    'net_weight'     => $item->weight > 0 ? $item->weight : null,
                    'net_weight'     => null,
                    'declared_value' => $item->amount,
                    'product_url'    => $item->link
                ]);
            }
        }

        /** @var Purchase $firstPurchase */
        $firstPurchase = $workOrder->purchases->first();

        /** @var User $user */
        $user = $firstPurchase->user;

        /** @var Address $address */
        $address = $firstPurchase->address;

        /** @var Service $service */
        $service = $workOrder->service;

        $data = [
            'service'               => $service ? $service->code : $this->detectServiceCode($purchase),
            'order_id'              => $firstPurchase->tracking,
            'sale_date'             => $firstPurchase->purchased_at->toIso8601String(),
            'delivery_duties_paid'  => ($service && $service->ddp) ? 'Y' : 'N',
            'origin_warehouse_code' => 'WH2255',
            'package'               => [
                'declared_value' => $workOrder->getDeclaredValue(),
                'weight'         => $shipment->getWeight(),
                'height'         => $shipment->getHeight(),
                'width'          => $shipment->getWidth(),
                'length'         => $shipment->getLength(),
            ],
            // Seller
            'shipper'               => [
                'name'        => 'Mailamericas Casilleros',
                'address1'    => '8800 NW 24th Terrace',
                'city'        => 'Doral',
                'state'       => 'Florida',
                'postal_code' => 33172,
                'country'     => 'US',
            ],
            // Buyer
            'buyer'                 => [
                'name'        => $user->full_name,
                'buyer_id'    => $user->identification,
                'locker_code' => $user->getLockerCode(),
                'address1'    => $this->prepareAddress1($address),
                'address2'    => $address->address2,
                'address3'    => $address->reference,
                'country'     => $address->getCountryCode(),
                'state'       => $address->state,
                'city'        => $address->city,
                'district'    => $address->township,
                'postal_code' => $address->postal_code,
                'email'       => $user->email,
                'phone'       => $user->phone,
            ],
            // Items
            'items'                 => $items->toArray(),
        ];

        return $data;
    }

    private function prepareAddress1(Address $address)
    {
        $output = $address->address1;

        if ($address->number) {
            $output .= " {$address->number}";
        }

        if ($address->floor) {
            $output .= " {$address->floor}";
        }

        if ($address->apartment) {
            $output .= " {$address->apartment}";
        }

        return $output;
    }

    /**
     * @param $response
     * @param Shipment $shipment
     * @return boolean
     * @throws Exception
     */
    private function parseResponse($response, Shipment &$shipment)
    {
        if (isset($response->error) && isset($response->message)) {
            throw new Exception($response->message);
        }

        if (!isset($response->data)) {
            throw new Exception('Missing data field in response.');
        }

        $shipment->setTracking($response->data->tracking);
        $shipment->setLabel($response->data->label);

        return true;
    }

    private function detectServiceCode(Purchase $purchase)
    {
        // Origin
        $origin = $purchase->getWarehouseCountryCode();

        // Destination
        $destination = $purchase->getAddressCountryCode();

        if ($origin == 'CN') {
            switch ($destination) {
                case 'AR':
                    return 'CNEMSAR';
                case 'MX':
                    return 'CN0030MX';
                case 'CO':
                    return 'CN0015CO';
                case 'CL':
                    return 'CN0015CL';
                case 'PE':
                    return 'CN0030PE';
            }
        } elseif ($origin == 'US') {
            switch ($destination) {
                case 'AR':
                    return 'USEMSAR';
                case 'MX':
                    return 'US0015MX';
                case 'CO':
                    return 'US0015CO';
                case 'CL':
                    return 'CN0030CL';
                case 'PE':
                    return 'USHYBPE';
                case 'EC':
                    return 'USCAS11EX';
            }
        } elseif ($origin == 'GB') {
            switch ($destination) {
                case 'AR':
                    return 'GB0015AR';
                case 'MX':
                    return 'GB0015MX';
                case 'CO':
                    return 'GB0030CO';
                case 'CL':
                    return 'GB0010CL';
                case 'PE':
                    return 'GBHYBPE';
            }
        }

        return 'registered';
    }
}
