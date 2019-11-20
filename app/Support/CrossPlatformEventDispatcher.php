<?php

namespace App\Support;

use App\Events\BaseEvent;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Events\Dispatcher;

class CrossPlatformEventDispatcher extends Dispatcher
{
    /**
     * Queue the handler class.
     *
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @return void
     */
    protected function queueHandler($class, $method, $arguments)
    {
        [$listener, $job] = $this->createListenerAndJob($class, $method, $arguments);

        $connection_name = $listener->connection ?? null;

        // Check for specific cross-platform events
        if (!empty($arguments) && is_array($arguments)) {
            foreach ($arguments as $event) {
                if ($event instanceof BaseEvent) {
                    /** @var BaseEvent $event */
                    $connection_name = $event->getQueueConnection();
                }
            }
        };

        /** @var QueueContract $connection */
        $connection = $this->resolveQueue()->connection(
            $connection_name ?? null
        );

        $queue = $listener->queue ?? null;

        isset($listener->delay)
            ? $connection->laterOn($queue, $listener->delay, $job)
            : $connection->pushOn($queue, $job);
    }
}