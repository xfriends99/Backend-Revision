<?php

namespace App\Services\Cloud;

use App\Models\User;
use Exception;
use Mixpanel;

class MixpanelService extends AbstractMultiAnalytics
{
    /**
     * @param string $event
     * @return bool
     */
    public function trackGuest($event)
    {
        try {
            // Get Instance
            $mp = Mixpanel::getInstance(config('services.mixpanel.token'));

            // Track event
            $mp->track($event);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param User $user
     * @param string $event
     * @return bool
     */
    public function trackUser(User $user, $event)
    {
        try {
            // Get Instance
            $mp = Mixpanel::getInstance(config('services.mixpanel.token'));

            // Identify user
            $mp->identify($user->id);
            $mp->people->set($user->id, [
                '$first_name' => $user->first_name,
                '$last_name'  => $user->last_name,
                '$email'      => $user->email,
                '$phone'      => $user->phone,
            ]);

            // Track event
            $mp->track($event);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
