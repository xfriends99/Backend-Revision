<?php

namespace App\Services\Cloud;

use App\Models\User;
use Exception;
use Illuminate\Support\Collection;

abstract class MultiAnalyticsFactory
{
    const MIXPANEL = 'mixpanel';
    const ACTIVECAMPAIGN = 'active-campaign';

    /**
     * @param string|null $platform
     * @return Collection
     */
    protected static function detectGateways($platform = null)
    {
        /** @var Collection $gateways */
        $gateways = collect();

        if (is_null($platform) or ($platform == self::MIXPANEL)) {
            $gateways->push(new MixpanelService());
        }

        if (is_null($platform) or ($platform == self::ACTIVECAMPAIGN)) {
            $gateways->push(new ActiveCampaignService());
        }

        return $gateways;
    }

    /**
     * @param User $user
     * @param string $event
     * @param string|null $platform
     * @return boolean
     */
    public static function trackUser(User $user, $event, $platform = null)
    {
        $gateways = self::detectGateways($platform);
        $total = $gateways->count();
        $successful = 0;

        /** @var AbstractMultiAnalytics $gateway */
        foreach ($gateways as $gateway) {
            try {
                $successful += $gateway->trackUser($user, $event);
            } catch (Exception $e) {
                logger("[MultiAnalytics] Error tracking event {$event} for user {$user->email} to gateway " . get_class($gateway));
                logger($e->getMessage());
                logger($e->getTraceAsString());
            }
        }

        return ($successful == $total);
    }

    /**
     * @param string $event
     * @param string|null $platform
     * @return boolean
     */
    public static function trackGuest($event, $platform = null)
    {
        $gateways = self::detectGateways($platform);
        $total = $gateways->count();
        $successful = 0;

        /** @var AbstractMultiAnalytics $gateway */
        foreach ($gateways as $gateway) {
            try {
                $successful += $gateway->trackGuest($event);
            } catch (Exception $e) {
                logger("[MultiAnalytics] Error tracking event {$event} for anonymous user to gateway " . get_class($gateway));
                logger($e->getMessage());
                logger($e->getTraceAsString());
            }
        }

        return ($successful == $total);
    }
}
