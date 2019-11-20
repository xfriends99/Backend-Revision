<?php

namespace App\Services\Cloud;

use App\Models\User;

abstract class AbstractMultiAnalytics
{
    /**
     * @param $event
     * @return boolean
     */
    abstract public function trackGuest($event);

    /**
     * @param User $user
     * @param $event
     * @return boolean
     */
    abstract public function trackUser(User $user, $event);
}
