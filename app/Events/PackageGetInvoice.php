<?php

namespace App\Events;

use App\Models\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PackageGetInvoice extends BaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  Package */
    public $package;

    /**
     * PackageGetInvoice constructor.
     * @param Package $package
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
}
