<?php

namespace App\Events;

use App\Models\Package;
use App\Services\Packages\Events\PackageEventEntity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventWasReceived extends BaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var PackageEventEntity */
    public $packageEventEntity;

    /** @var Package */
    public $package;

    /**
     * Create a new event instance.
     *
     * @param PackageEventEntity $packageEventEntity
     * @param Package $package
     * @return void
     */
    public function __construct(PackageEventEntity $packageEventEntity, Package $package)
    {
        $this->packageEventEntity = $packageEventEntity;
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
}
