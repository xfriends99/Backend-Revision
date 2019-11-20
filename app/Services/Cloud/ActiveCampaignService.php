<?php

namespace App\Services\Cloud;

use ActiveCampaign;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\Response;

class ActiveCampaignService extends AbstractMultiAnalytics
{
    /**
     * @param string $event
     * @return boolean
     */
    public function trackGuest($event)
    {
        try {
            $activeCampaign = new ActiveCampaign(env('ACTIVECAMPAIGN_URL'), env('ACTIVECAMPAIGN_API_KEY'));
            $activeCampaign->track_actid = env('ACTIVECAMPAIGN_ACTID');
            $activeCampaign->track_key = env('ACTIVECAMPAIGN_KEY');

            $payload = [
                "event"     => $event,
                "eventdata" => Carbon::now()->toDateTimeString()
            ];

            logger("[ActiveCampaign] Track guest request");
            logger(print_r($payload, true));

            /** @var Response $response */
            $response = $activeCampaign->api("tracking/log", $payload);

            logger("[ActiveCampaign] Track guest response");
            logger(print_r($response, true));

            if (isset($response->success) && $response->success == 1) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return false;
        }
    }

    /**
     * @param User $user
     * @param string $event
     * @return boolean
     */
    public function trackUser(User $user, $event)
    {
        try {
            $activeCampaign = new ActiveCampaign(env('ACTIVECAMPAIGN_URL'), env('ACTIVECAMPAIGN_API_KEY'));
            $activeCampaign->track_actid = env('ACTIVECAMPAIGN_ACTID');
            $activeCampaign->track_key = env('ACTIVECAMPAIGN_KEY');
            $activeCampaign->track_email = $user->email;

            $payload = [
                'event'     => $event,
                'eventdata' => Carbon::now()->toDateTimeString()
            ];

            logger("[ActiveCampaign] Track user request");
            logger(print_r($payload, true));

            /** @var Response $response */
            $response = $activeCampaign->api("tracking/log", $payload);

            logger("[ActiveCampaign] Track user response");
            logger(print_r($response, true));

            if (isset($response->success) && $response->success == 1) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return false;
        }
    }

    /**
     * @param User $user
     * @return boolean
     */
    public function createContact(User $user)
    {
        try {
            $activeCampaign = new ActiveCampaign(env('ACTIVECAMPAIGN_URL'), env('ACTIVECAMPAIGN_API_KEY'));
            $activeCampaign->track_actid = env('ACTIVECAMPAIGN_ACTID');
            $activeCampaign->track_key = env('ACTIVECAMPAIGN_KEY');

            $payload = [
                'email'      => $user->email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'phone'      => $user->phone,
                'field'      => [
                    '%BIRTHDAY%'            => Carbon::parse($user->born_at)->format("d/m/Y"), //(29/12/2019)
                    '%FECHA_REGISTRO%'      => Carbon::parse($user->created_at)->format("d/m/Y"), // (20/8/19)
                    '%NUMERO_DE_CASILLERO%' => $user->getLockerCode(),
                    '%COUNTRY%'             => $user->getCountryName(),
                ]
            ];

            logger("[ActiveCampaign] Sync contact request");
            logger(print_r($payload, true));

            /** @var Response $response */
            $response = $activeCampaign->api("contact/sync", $payload);

            logger("[ActiveCampaign] Sync contact response");
            logger(print_r($response, true));

            if (isset($response->success) && $response->success == 1) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            logger("[ActiveCampaign] Could not create contact for {$user->email}");
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return false;
        }
    }
}
