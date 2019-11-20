<?php

namespace App\Events;

use App\Models\Package;
use App\Models\Platform;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentWasProcessed extends BaseEvent
{
    use Dispatchable, SerializesModels;

    public $package;

    /**
     * Create a new event instance.
     *
     * @param Package $package
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->package->getUserPlatform() ?? current_platform();
    }
}
