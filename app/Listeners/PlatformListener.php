<?php

namespace App\Listeners;

use App\Events\BaseEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PlatformListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @param BaseEvent $baseEvent
     * @return bool
     */
    protected function isValidPlatform(BaseEvent $baseEvent)
    {
        return (current_platform()->id == $baseEvent->getPlatform()->id);
    }
}