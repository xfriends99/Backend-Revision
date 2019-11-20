<?php

namespace App\Events;

use App\Models\Platform;

abstract class BaseEvent
{
    /**
     * @return string
     */
    public function getQueueConnection()
    {
        /** @var Platform $platform */
        $platform = $this->getPlatform();

        // If platforms differ, we need to make sure the event will run in the other platform.
        if (current_platform()->id != $platform->id) {
            if ($platform->isCorreosEcuador()) {
                return 'beanstalkd_ecuador';
            } elseif ($platform->isMailamericas()) {
                return 'beanstalkd_mailamericas';
            }
        }

        return config('queue.default');
    }

    /**
     * @return Platform
     */
    public function getPlatform()
    {
        return current_platform();
    }
}
